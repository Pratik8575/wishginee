<?php

namespace Wishginee\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

use Wishginee\Campaign;
use Wishginee\Donation;
use Wishginee\Feed;
use Wishginee\Follow;
use Wishginee\Foundation\Response;
use Wishginee\Foundation\Transformer;
use Wishginee\Http\Requests;
use Wishginee\User;

class CampaignsController extends Controller
{

    /**
     * Creates Campaign
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function create(Request $request){

        $this->authorize('create', Campaign::class);

        $campaign = new Campaign();
        $campaign->profile_photo = Controller::PROFILE_PHOTO_URL;
        $campaign->cover_photo = Controller::COVER_PHOTO_URL;
        $campaign->user_id = auth()->user()->_id;
        $campaign->fund_raised = 0;
        $campaign->status = Campaign::STATUS[0];
        $campaign->save();
        return Response::raw(201, $campaign);
    }

    /**
     * List campaign associated with $id
     * @param $campaignId
     * @return Response
     */
    public function showCampaignById($campaignId){
        $campaign = Campaign::query()->findOrFail($campaignId);
        $followers = Follow::query()->where('followable_id', $campaignId)->get();
        $campaign->comments;
        $donations = Donation::query()->where('donee_id', $campaignId)->where('donee_type', 'campaign')->get();
        return Response::raw(200, array_merge($campaign->toArray(), ["followers" => $followers, "donations" => $donations]));
    }

    /**
     * Lists all the campaigns
     * @return Response
     */
    public function show(){
        return Response::raw(200, Campaign::query()->get());
    }

    /**
     * Verify the campaign by admin
     * @param $campaignId
     * @return Response
     */
    public function updateStatus($campaignId){
        $this->authorize('updateStatus', auth()->user());
        $campaign = Campaign::query()->findOrFail($campaignId);
        $campaign->status = Campaign::STATUS[1];
        $campaign->is_approval = false;
        $campaign->save();

        $user = $campaign->user;


        // Make feed after approval

        $feed = new Feed();
        $feed->type = 'campaigns';
        $feed->user_id = $campaign->user_id;
        $feed->user_name = $user->first_name." ".$user->last_name;
        $feed->type_id = $campaign->_id;
        $feed->type_name = $campaign->name;
        $feed->text = 'created campaign';
        $feed->cover_photo = $campaign->cover_photo;
        $feed->category = $campaign->category;
        $feed->fund_needed = $campaign->fund_needed;
        $feed->fund_raised = $campaign->fund_raised;
        $feed->email = $campaign->contact_email;
        $feed->mobile = $campaign->contact_mobile;
        $feed->date = Carbon::now();
        $feed->save();
        return Response::raw(200, $campaign);
    }

    /**
     * Updates the Campaign
     * @param Request $request
     * @param $campaignId
     * @return Response
     */
    public function update(Request $request, $campaignId){
        $campaign = Campaign::query()->findOrFail($campaignId);
        $this->authorize('update', $campaign);

        $validator = \Validator::make($request->all(),[
            'profile_photo' => 'url',
            'cover_photo' => 'url',
            'contact_email' => 'email|max:255',
            'contact_mobile' => 'digits:10',
            'name' => 'string|max:50',
            'abstract' => 'string|max:100',
            'category' => 'string',
            'fund_needed' => 'integer',
            'story' => 'string|max:600',
            'date' => 'string',
            'location' => 'string|max:500',
        ]);

        if($validator->fails()){
            return Response::error(422, $validator->errors());
        }

        $updateables = ['name', 'abstract', 'story', 'location', 'profile_photo', 'cover_photo', 'contact_email', 'contact_mobile', 'category', 'fund_needed', 'is_approval'];

        foreach ($updateables as $updateable){
            if($request->has($updateable)){
                $campaign->fill([$updateable => $request->get($updateable)]);
            }
        }

        if($request->has('date')){
            $date = explode('/', $request->get('date')); // will have format dd/mm/yyyy
            $campaign->date = Carbon::create($date[2], $date[1], $date[0]);
        }
        if($request->has('campaign_by')){
            $campaign->campaign_by = $request->get('campaign_by');
        }else{
            $campaign->campaign_by = auth()->user()->getNameAttribute();
        }

        $campaign->save();
        return Response::raw(200, $campaign);
    }

    /**
     * Deletes the campaign associated with $campaignId
     * @param $campaignId
     * @return Response
     * @throws \Exception
     */
    public function delete($campaignId){
        $campaign = Campaign::query()->findOrFail($campaignId);
        $this->authorize('delete', $campaign);

        $campaign->delete();
        return Response::raw(200,[]);
    }
}

<?php

namespace Wishginee\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Wishginee\Donation;
use Wishginee\Follow;
use Wishginee\Form;
use Wishginee\Foundation\Response;
use Wishginee\Foundation\Transformer;
use Wishginee\Http\Requests;
use Wishginee\Notification;
use Wishginee\User;

class UsersController extends Controller
{
    /**
     * Returns User details associated with $id
     * @param $id
     * @return Response
     */
    public function show($id){
        /**
         * If user uses /user/me endpoint then also it is valid.
         */
        if($id == "me"){
            $id = $this->guard()->id();
        }
        $user = User::query()->findOrFail($id);
        $user->campaigns;
        $user->events;
        $user->notifications;
        $unreadNotifications = Notification::query()->where('to_user_id', $id)->where('is_read', false)->get()->count();
        $followers = Follow::query()->where('followable_id', $id)->get();
        $followings = Follow::query()->where('follower_id', $id)->get();
        $donations = Donation::query()->where('user_id', $id)->get();
        $ngoDonations = Form::query()->where('to_user_type', 'NGO')->where('user_id', $id)->get(['amount']);
        $ngoDonationCount = 0;

        foreach ($ngoDonations as $ngoDonation){
            $ngoDonationCount += $ngoDonation->amount;
        }

        $hhDonations = Form::query()->where('to_user_type', 'HH')->where('user_id', $id)->get(['amount']);
        $hhDonationCount = 0;

        foreach ($hhDonations as $hhDonation){
            $hhDonationCount += $hhDonation->amount;
        }

        $campaignDonations = Form::query()->where('donee_type', 'campaign')->where('user_id', $id)->get(['amount']);
        $campaignDonationCount = 0;

        foreach ($campaignDonations as $campaignDonation){
            $campaignDonationCount += $campaignDonation->amount;
        }

        $eventDonations = Form::query()->where('donee_type', 'event')->where('user_id', $id)->get(['amount']);
        $eventDonationCount = 0;


        foreach ($eventDonations as $eventDonation){
            $eventDonationCount += $eventDonation->amount;
        }

        return Response::raw(200,
            array_merge($user->toArray(),
                [
                    "followers" => $followers,
                    "followings" => $followings,
                    "donations" => $donations,
                    "unread_notifications" => $unreadNotifications,
                    "ngo_donation_count" => $ngoDonationCount,
                    "hh_donation_count" => $hhDonationCount,
                    "campaign_donation_count" => $campaignDonationCount,
                    "event_donation_count" => $eventDonationCount
                ]
            )
        );
    }

    /**
     * Updates the User associated with $id
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function update(Request $request, $id){
        $user = User::query()->findOrFail($id);
        $this->authorize('update', $user);
        
        $validator = \Validator::make($request->all(),[
            'first_name' => 'string|min:4',
            'last_name' => 'string|min:4',
            'password' => 'string|min:5',
            'website' => 'url',
            'profile_photo' => 'url',
            'cover_photo' => 'url',
            'city' => 'string',
            'country' => 'string',
            'description' => 'string|max:400',
            'group' => 'string|in:PU,SC,HH,NGO,SU',
            'founders' => 'array',
            'founders.*' => 'string'
        ]);


        if($validator->fails()){
            return Response::error(422, $validator->errors());
        }

        $updatables = ['first_name', 'last_name', 'mobile', 'website', 'country', 'city', 'description', 'profile_photo', 'cover_photo'];

        foreach ($updatables as $updatable){
            if($request->has($updatable)){
                $user->setAttribute($updatable, $request->get($updatable));
            }
        }
        if($request->has('password')){
            $user->password = bcrypt($request->get('password'));
        }

        if($request->has('founders')){
            $this->authorize('addFounders', $user);
            $newFounders = [];
            if($user->founders != null){
                $newFounders = $user->founders;
            }
            foreach ($request->get('founders') as $founder){
                array_push($newFounders, $founder);
            }
            $user->founders = $newFounders;
        }
        
        if($request->has('group')){
            $this->authorize('updateGroup', $user);
            $user->group = $request->get('group');
        }

        if($request->has('is_approved')){
            $this->authorize('approveUser', $user);
            $user->is_approved = true;
        }
        
        $user->save();
        return Response::raw(200, $user);
    }
    
    /**
     * Delete's the user associated with the $id
     * @param $id
     * @return Response
     * @throws \Exception
     */
    public function delete($id){
        $user = User::query()->findOrFail($id);
        $this->authorize('delete', $user);
        
        $user->delete();
        return Response::raw(200, []);
    }
}

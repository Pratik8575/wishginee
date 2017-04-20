<?php

namespace Wishginee\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

use Wishginee\Donation;
use Wishginee\Event;
use Wishginee\Feed;
use Wishginee\Follow;
use Wishginee\Foundation\Response;
use Wishginee\Http\Requests;

class EventsController extends Controller
{
    /**
     * Creates Event
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function create(Request $request){
        $this->authorize('create', Event::class);

        $event = new Event();
        $event->profile_photo = Controller::PROFILE_PHOTO_URL;
        $event->cover_photo = Controller::COVER_PHOTO_URL;
        $event->user_id = auth()->user()->_id;
        $event->fund_raised = 0;
        $event->status = Event::STATUS[0];
        $event->save();
        return Response::raw(201, $event);
    }

    /**
     * List of all the events
     * @return Response
     */
    public function show(){
        return Response::raw(200, Event::query()->get());
    }

    /**
     * Returns event by its $id
     * @param $id
     * @return Response
     */
    public function getById($id){
        $event = Event::query()->findOrFail($id);
        $followers = Follow::query()->where('followable_id', $id)->get();
        $event->comments;
        $donations = Donation::query()->where('donee_id', $id)->where('donee_type', 'event')->get();
        return Response::raw(200, array_merge($event->toArray(), ["followers" => $followers, "donations" => $donations]));
    }


    /**
     * Verify the event by admin
     * @param $eventId
     * @return Response
     * @internal param $campaignId
     */
    public function updateStatus($eventId){
        $this->authorize('updateStatus', auth()->user());
        $event = Event::query()->findOrFail($eventId);
        $event->status = Event::STATUS[1];
        $event->save();

        $user = $event->user;

        // Make feed after approval

        $feed = new Feed();
        $feed->type = 'events';
        $feed->user_id = $event->user_id;
        $feed->user_name = $user->first_name." ".$user->last_name;
        $feed->type_id = $event->_id;
        $feed->type_name = $event->name;
        $feed->text = 'created event';
        $feed->cover_photo = $event->cover_photo;
        $feed->category = $event->category;
        $feed->fund_needed = $event->fund_needed;
        $feed->fund_raised = $event->fund_raised;
        $feed->email = $event->contact_email;
        $feed->mobile = $event->contact_mobile;
        $feed->date = Carbon::now();
        $feed->save();
        return Response::raw(200, $event);
    }


    /**
     * Delete's the event
     * @param $id
     * @return Response
     * @throws \Exception
     */
    public function delete($id){
        $event = Event::query()->findOrFail($id);
        $this->authorize('delete', $event);

        $event->delete();

        return Response::raw(200, []);
    }

    /**
     * Updates the event
     * @param Request $request
     * @param $id
     * @return Response
     * @throws \Exception
     */
    public function update(Request $request, $id){
        $event = Event::query()->findOrFail($id);
        $this->authorize('update', $event);

        $validator = \Validator::make($request->all(),[
            'name' => 'string|max:50',
            'abstract' => 'string|max:250',
            'location' => 'string|max:300',
            'profile_photo' => 'url',
            'cover_photo' => 'url',
            'fund_needed' => 'numeric',
            'contact_email' => 'email|max:255',
            'contact_mobile' => 'digits:10',
            'story' => 'string|max:600',
            'date' => 'string',
            'event_by' => 'string'
        ]);

        if($validator->fails()){
            return Response::error(422, $validator->errors());
        }
        
        $updateables = ["name", "abstract", "location", "profile_photo", "cover_photo", "fund_needed", "contact_email", "contact_mobile", "story", "is_approval", "category"];
        $event->fillable($updateables);
        foreach ($updateables as $updateable){
            if($request->has($updateable)){
                $event->fill([$updateable => $request->get($updateable)]);
            }
        }
        $event->save();

        if($request->has('date')){
            $date = explode('/', $request->get('date')); // will have format dd/mm/yyyy
            $event->date = Carbon::create($date[2], $date[1], $date[0]);
        }

        if($request->has("event_by")){
            $event->event_by = $request->get('event_by');
        }else{
            $event->event_by = auth()->user()->getNameAttribute();
        }

        $event->save();
        return Response::raw(200, $event);
    }
    

}

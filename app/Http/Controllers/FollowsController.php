<?php

namespace Wishginee\Http\Controllers;

use Illuminate\Http\Request;

use Wishginee\Campaign;
use Wishginee\Event;
use Wishginee\Follow;
use Wishginee\Foundation\Response;
use Wishginee\Http\Requests;
use Wishginee\Notification;
use Wishginee\User;

class FollowsController extends Controller
{
    /**
     * Checks whether already following to a type of model or not
     * @param $followerId
     * @param $followableId
     * @return bool
     */
    protected function checkAlreadyFollowing($followerId, $followableId){
        $count = Follow::query()->where('follower_id', $followerId)->where('followable_id', $followableId)->get()->count();
        if($count == 0){
            return false;
        }else{
            return true;
        }
    }


    /**
     * Creates a follow with the particular model
     * @param $id
     * @return Response
     */
    public function create($id){        
        $user = User::query()->find($id);
        $follow = new Follow();
        if(!is_null($user)){
            if(!$this->checkAlreadyFollowing(auth()->user()->_id, $user->_id)){
                $follow->follower_user_name = $user->first_name." ".$user->last_name;
                $follow->following_user_name = auth()->user()->getNameAttribute();
                $follow->follower_profile_photo = $user->profile_photo;
                $follow->following_profile_photo = auth()->user()->profile_photo;
                $follow->follower_id = auth()->user()->_id;
                $user->followers()->save($follow);

                $notification = new Notification();
                $notification->is_read = false;
                $notification->to_user_id = $id;
                $notification->from_user_id = auth()->user()->_id;
                $notification->from_user_name = auth()->user()->getNameAttribute();
                $notification->to_user_name = $user->first_name." ".$user->last_name;
                $notification->from_user_profile = auth()->user()->profile_photo;
                $notification->text = " started following you.";
                $notification->save();

                return Response::raw(201,$follow);
            }else{
                return Response::error(422, "Already Following");
            }
        }
        
        $event = Event::query()->find($id);
        
        if(!is_null($event)){
            if(!$this->checkAlreadyFollowing(auth()->user()->_id, $event->_id)){
                $follow->follower_user_name = $event->name;
                $follow->following_user_name = auth()->user()->getNameAttribute();
                $follow->follower_profile_photo = $event->cover_photo;
                $follow->following_profile_photo = auth()->user()->profile_photo;
                $follow->follower_id = auth()->user()->_id;
                $event->followers()->save($follow);

                $notification = new Notification();
                $notification->is_read = false;
                $notification->to_user_id = $event->user_id;
                $notification->from_user_id = auth()->user()->_id;
                $notification->from_user_name = auth()->user()->getNameAttribute();
                $notification->to_user_name = $event->name;
                $notification->from_user_profile = auth()->user()->profile_photo;
                $notification->text = " started following your event ";
                $notification->save();

                return Response::raw(201, $follow);
            }else{
                return Response::error(422, "Already Following");
            }
        }

        $campaign = Campaign::query()->find($id);

        if(!is_null($campaign)){
            if(!$this->checkAlreadyFollowing(auth()->user()->_id, $campaign->_id)){
                $follow->follower_user_name = $campaign->name;
                $follow->following_user_name = auth()->user()->getNameAttribute();
                $follow->follower_profile_photo = $campaign->cover_photo;
                $follow->following_profile_photo = auth()->user()->profile_photo;
                $follow->follower_id = auth()->user()->_id;
                $campaign->followers()->save($follow);

                $notification = new Notification();
                $notification->is_read = false;
                $notification->to_user_id = $campaign->user_id;
                $notification->from_user_id = auth()->user()->_id;
                $notification->from_user_name = auth()->user()->getNameAttribute();
                $notification->to_user_name = $campaign->name;
                $notification->from_user_profile = auth()->user()->profile_photo;
                $notification->text = " started following your campaign ";
                $notification->save();

                return Response::raw(201, $follow);
            }else{
                return Response::error(422, "Already Following");
            }
        }
        return Response::error(404, "Not Found");
    }

    /**
     * Deletes the followings
     * @param $id
     * @return Response
     */
    public function delete($id){
        $user = User::query()->find($id);
        if(!is_null($user)){
            if($this->checkAlreadyFollowing(auth()->user()->_id, $user->_id)){
                $follow = Follow::query()->where('follower_id', auth()->user()->_id)->where('followable_id', $id);
                if(!is_null($follow->get())){
                    $follow->delete();
                    return Response::raw(200, []);
                }
            }
        }

        $event = Event::query()->find($id);

        if(!is_null($event)){
            if($this->checkAlreadyFollowing(auth()->user()->_id, $event->_id)){
                $follow = Follow::query()->where('follower_id', auth()->user()->_id)->where('followable_id', $id);
                if(!is_null($follow->get())){
                    $follow->delete();
                    return Response::raw(200, []);
                }
            }
        }

        $campaign = Campaign::query()->find($id);

        if(!is_null($campaign)){
            if($this->checkAlreadyFollowing(auth()->user()->_id, $campaign->_id)){
                $follow = Follow::query()->where('follower_id', auth()->user()->_id)->where('followable_id', $id);
                if(!is_null($follow->get())){
                    $follow->delete();
                    return Response::raw(200, []);
                }
            }
        }
        return Response::error(404, "Not Found");
    }
}

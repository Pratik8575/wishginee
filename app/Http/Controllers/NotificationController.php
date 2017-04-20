<?php

namespace Wishginee\Http\Controllers;

use Illuminate\Http\Request;

use Wishginee\Feed;
use Wishginee\Foundation\Response;
use Wishginee\Http\Requests;
use Wishginee\Notification;
use Wishginee\Socket;
use Wishginee\User;

class NotificationController extends Controller
{
    /**
     * Returns all the notifications for the user.
     * @return Response
     */
    public function getUserNotifications($id){
        $user = User::query()->findOrFail($id);
        $notifications = Notification::query()->where('to_user_id', $user->_id)->get();
        $unreadNotifications = Notification::query()->where('to_user_id', $user->_id)->where('is_read', false)->get()->count();
        return Response::raw(200, array_merge(["notifications" => $notifications->toArray()], ["unread_notifications" => $unreadNotifications]));
    }


    /**
     * Updates the notification seen.
     * @param $id
     * @return Response
     */
    public function updateSeen($id){
        $notifications = Notification::query()->where('to_user_id', $id)->where('is_read', false);
        $notifications->update(['is_read' => true]);
        return Response::raw(200,[]);
    }

    /**
     * Adds user to the connected list.
     * @param Request $request
     * @return Response
     */
    public function addUserConnected(Request $request){
        $socket = Socket::query()->find($request->get('user_id'));
        if(is_null($socket)){
            $socket = new Socket();
            $socket->user_id = $request->get('user_id');
            $socket->socket = $request->get('socket');
            $socket->save();
            return Response::raw(201, $socket);
        }else if(!is_null($socket)){
            $socket->socket = $request->get('socket');
            $socket->update(['user_id' => $request->get('user_id'), 'socket' => $request->get('socket')]);
            return Response::raw(200, $socket);
        }else{
            return Response::raw(200, []);
        }
    }

    /**
     * Returns user socket associated with its id.
     * @param $userId
     * @return Response
     */
    public function getUserSocket($userId){
        $socket = Socket::query()->findOrFail($userId);
        return Response::raw(200, $socket);
    }


    /**
     * Disconnects the user from connected list.
     * @param $userId
     * @return Response
     * @throws \Exception
     */
    public function deleteUserSocket($userId){
        $socket = Socket::query()->findOrFail($userId);
        $socket->delete();
        return Response::raw(200, []);
    }


    /**
     * Get all the sockets of the connected clients.
     * @param Request $request
     * @return Response
     */
    public function getAllClients(Request $request){
        $sockets = Socket::query()->get();
        return Response::raw(200, $sockets);
    }


    /**
     * Returns all the feeds.
     * @return Response
     */
    public function getFeeds(){
        $feeds = Feed::query()->get();
        return Response::raw(200,["feeds" => $feeds]);
    }

}

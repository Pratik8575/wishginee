<?php

namespace Wishginee\Http\Controllers;

use Illuminate\Http\Request;

use Wishginee\Campaign;
use Wishginee\Comment;
use Wishginee\Event;
use Wishginee\Foundation\Response;
use Wishginee\Http\Requests;

class CommentsController extends Controller
{
    /**
     * Creates the comment
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function create(Request $request, $id){
        $validator = \Validator::make($request->all(),[
            'message' => 'required|string|max:200'
        ]);

        if($validator->fails()){
            return Response::error(422, $validator->errors());
        }
        
        $event = Event::query()->find($id);
        $comment = new Comment();
        if(!is_null($event)){
            $comment->user_name = auth()->user()->getNameAttribute();
            $comment->message = $request->get('message');
            $comment->profile_photo = auth()->user()->profile_photo;
            $comment->cover_photo = auth()->user()->cover_photo;
            $comment->user_id = auth()->user()->_id;
            $event->comments()->save($comment);
            return Response::raw(201, $comment);
        }

        $campaign = Campaign::query()->find($id);
        if(!is_null($campaign)){
            $comment->user_name = auth()->user()->getNameAttribute();
            $comment->message = $request->get('message');
            $comment->profile_photo = auth()->user()->profile_photo;
            $comment->cover_photo = auth()->user()->cover_photo;
            $comment->user_id = auth()->user()->_id;
            $campaign->comments()->save($comment);
            return Response::raw(201, $comment);
        }
        return Response::error(404,["message" => "Not Found"]);
    }

    
    /**
     * Updates the comment using its associated $id
     * @param Request $request
     * @param $commentId
     * @return Response
     */
    public function update(Request $request, $commentId){
        $validator = \Validator::make($request->all(),[
            'message' => 'string|required|max:200' 
        ]);
        
        if($validator->fails()){
            return Response::error(422, $validator->errors());
        }
        $comment = Comment::query()->findOrFail($commentId);
        
        $comment->message = $request->get('message');
        $comment->save();
        return Response::raw(200, $comment);
    }

    /**
     * Delete the comment associated with $id
     * @param $commentId
     * @return Response
     * @throws \Exception
     */
    public function delete($commentId){
        $comment = Comment::query()->findOrFail($commentId);
        $comment->delete();
        return Response::raw(200, []);
    }
}

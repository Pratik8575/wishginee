<?php

namespace Wishginee\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Wishginee\Campaign;
use Wishginee\Event;
use Wishginee\User;

class FollowPolicy
{
    use HandlesAuthorization;


    /**
     * Same user cannot follow itself
     * @param User $user
     * @param User $subject
     * @return bool
     */
    public function createUserFollow(User $user, User $subject){
        return $user->_id != $subject->_id;
    }

    /**
     * @param User $user
     * @param Event $subject
     * @return bool
     * @internal param Event $event
     */
    public function createEventFollow(User $user, Event $subject){
        return $user->_id != $subject->user_id;
    }

    /**
     * @param User $user
     * @param Campaign $subject
     * @return bool
     * @internal param Campaign $campaign
     */
    public function createCampaignFollow(User $user, Campaign $subject){
        return $user->_id != $subject->user_id;
    }
}

<?php

namespace Wishginee\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Wishginee\Event;
use Wishginee\User;

class EventPolicy
{
    use HandlesAuthorization;

    /**
     * Only super user will get all the permissions
     * @param User $user
     * @param $abilities
     * @return bool
     */
    public function before(User $user, $abilities){
        if($user->group == "SU" && $user->is_approved){
            return true;
        }
    }

    /**
     * User belonging to particular group will get create permission
     * @param User $user
     * @return bool
     */
    public function create(User $user){
        return in_array($user->group, ["NGO", "HH"]);
    }

    /**
     * Only SU has permission to update the status of the event
     * @param User $user
     * @param User $subject
     * @return bool
     */
    public function updateStatus(User $user, User $subject){
        return $user->group == "SU";
    }

    /**
     * User who created event will get update permission
     * @param User $user
     * @param Event $event
     * @return bool
     */
    public function update(User $user, Event $event){
        return ($user->_id == $event->user_id) && ($event->status == Event::STATUS[0]);
    }

    /**
     * User who created event will get the permission
     * @param User $user
     * @param Event $event
     * @return bool
     */
    public function delete(User $user, Event $event){
        return ($user->_id == $event->user_id)  && ($event->status = Event::STATUS[0]);
    }
    
}

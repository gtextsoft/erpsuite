<?php

namespace Modules\ToDo\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ToDoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'assigned_to' => $this->assigned_to,
            'assigned_users' => $this->getAssignedUsers(),
            'description' => json_decode($this->description, true),
            'status' => $this->status,
            'status_name' => $this->getStatusName(),
            'priority' => $this->priority,
            'start_date' => $this->start_date,
            'due_date' => $this->due_date,
            'comments' => $this->comments,
            'module' => $this->module,
            'sub_module' => $this->sub_module,
            'assign_by' => $this->assign_by,
            'assign_by_name' => $this->getAssignByName(),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get assigned users names.
     *
     * @return array
     */
    protected function getAssignedUsers()
    {
        $userIds = explode(',', $this->assigned_to);
        $users = \App\Models\User::whereIn('id', $userIds)->get();
        
        return $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];
        });
    }

    /**
     * Get status name.
     *
     * @return string|null
     */
    protected function getStatusName()
    {
        $stage = \Modules\ToDo\Entities\TodoStage::find($this->status);
        return $stage ? $stage->name : null;
    }

    /**
     * Get assign by user name.
     *
     * @return array|null
     */
    protected function getAssignByName()
    {
        $user = \App\Models\User::find($this->assign_by);
        
        return $user ? [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ] : null;
    }
}

class TodoStageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'order' => $this->order,
            'color' => $this->color,
            'created_by' => $this->created_by,
            'workspace_id' => $this->workspace_id,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'tasks' => isset($this->tasks) ? ToDoResource::collection($this->tasks) : null,
        ];
    }
}
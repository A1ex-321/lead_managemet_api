<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Lead;

class GroupService
{
    public function getAllGroups()
    {
        $groups = Group::all();

        // Iterate over each group to calculate the lead count
        foreach ($groups as $group) {
            // Get the categories and tags associated with the group
            $categories = $group->category;
            $tags = $group->tags;

            // Build leads query
            $leadsQuery = Lead::query();

            // Filter leads by tags
            if (!empty($tags)) {
                foreach ($tags as $tag) {
                    $leadsQuery->orWhereJsonContains('tags', $tag);
                }
            }

            // Get the leads that match any tag
            $leads = $leadsQuery->get();

            // Filter leads based on matching categories
            $filteredLeads = $leads->filter(function ($lead) use ($categories) {
                return in_array($lead->category, $categories);
            });

            // Count the filtered leads
            $leadCount = $filteredLeads->count();

            // Assign lead count to the group object
            $group->lead_count = $leadCount;
        }

        return $groups;
    }

    public function getGroupById($id)
    {
        // Find the group by ID
        $group = Group::findOrFail($id);


        // Get the categories and tags associated with the group
        $categories = $group->category;
        $tags = $group->tags;

        $leadsQuery = Lead::query();

        if (!empty($tags)) {
            foreach ($tags as $tag) {
                $leadsQuery->orWhereJsonContains('tags', $tag);
            }
        }

        // Get the leads that match any tag
        $leads = $leadsQuery->get();

        // Filter leads based on matching categories
        $filteredLeads = $leads->filter(function ($lead) use ($categories) {
            return in_array($lead->category, $categories);
        });

        // Count the filtered leads
        $leadCount = $filteredLeads->count();

        // Prepare the response data
        $response = [
            'leads' => $filteredLeads,
            'lead_count' => $leadCount,
        ];

        return $response;
    }

    public function destroy($id)
    {
        $group = Group::findOrFail($id);
        return $group->delete();
    }
}

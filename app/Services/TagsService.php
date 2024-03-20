<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Tags;

class TagsService
{

    public function saveTags($data)
    {
        Tags::create([
            'tags' => $data['tags'],
        ]);

        return response()->json(['message' => 'Tags created successfully'], 201);
    }

    public function getTags($data)
    {
        if ($data['formatted']) {
            $data = Tags::all()->pluck('tags')->toArray();
        } else {
            $data = Tags::all();
        }

        return response()->json(['tags' => $data]);
    }

    public function destroy($id)
    {
        $tags = Tags::findOrFail($id);
        return $tags->delete();
    }
}

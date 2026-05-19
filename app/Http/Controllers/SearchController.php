<?php

namespace App\Http\Controllers;

use App\Models\BoardList;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('q', ''));

        if ($query === '') {
            return response()->json(['lists' => [], 'tasks' => []]);
        }

        $tasks = Task::search($query)
            ->take(20)
            ->get()
            ->load('list')
            ->map(fn (Task $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'list_id' => $task->board_list_id,
                'list_name' => $task->list?->name,
            ])
            ->values();

        $lists = BoardList::search($query)
            ->take(10)
            ->get()
            ->map(fn (BoardList $list) => [
                'id' => $list->id,
                'name' => $list->name,
            ])
            ->values();

        return response()->json([
            'lists' => $lists,
            'tasks' => $tasks,
        ]);
    }
}

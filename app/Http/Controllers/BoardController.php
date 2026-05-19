<?php

namespace App\Http\Controllers;

use App\Models\BoardList;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BoardController extends Controller
{
    public function index(): View
    {
        $lists = BoardList::with(['tasks.attachments'])->orderBy('position')->get();
        return view('board.index', compact('lists'));
    }

    public function storeList(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => 'required|string|max:120']);
        $position = (int) (BoardList::max('position') ?? -1) + 1;
        $list = BoardList::create(['name' => $data['name'], 'position' => $position]);
        return response()->json($list);
    }

    public function destroyList(BoardList $list): JsonResponse
    {
        $list->delete();
        return response()->json(['ok' => true]);
    }

    public function storeTask(Request $request): JsonResponse
    {
        $data = $request->validate([
            'board_list_id' => 'required|exists:board_lists,id',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
        ]);
        $position = (int) (Task::where('board_list_id', $data['board_list_id'])->max('position') ?? -1) + 1;
        $task = Task::create([
            'board_list_id' => $data['board_list_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'position' => $position,
        ]);
        return response()->json($task);
    }

    public function updateTask(Request $request, Task $task): JsonResponse
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:200',
            'description' => 'sometimes|nullable|string',
        ]);
        $task->update($data);
        return response()->json($task);
    }

    public function destroyTask(Task $task): JsonResponse
    {
        $task->delete();
        return response()->json(['ok' => true]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lists' => 'required|array',
            'lists.*.id' => 'required|exists:board_lists,id',
            'lists.*.task_ids' => 'array',
            'lists.*.task_ids.*' => 'integer|exists:tasks,id',
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['lists'] as $listIndex => $listData) {
                BoardList::where('id', $listData['id'])->update(['position' => $listIndex]);
                foreach ($listData['task_ids'] ?? [] as $taskIndex => $taskId) {
                    Task::where('id', $taskId)->update([
                        'board_list_id' => $listData['id'],
                        'position' => $taskIndex,
                    ]);
                }
            }
        });

        return response()->json(['ok' => true]);
    }
}

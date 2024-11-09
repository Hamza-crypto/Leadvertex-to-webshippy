<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::query();

        if (Auth::user()->role == 'admin') {
            $tasks = $query->filter($request->user, $request->status)->with(['assignedUser'])->get();
            $users = User::all();
        } else {
            $tasks = $query->filter(Auth::user()->id, $request->status)->with(['assignedUser'])->get();
            $users = [];
        }

        return view('pages.tasks.index', compact('tasks', 'users'));
    }

    public function create()
    {
        if (Auth::user()->role != 'admin') {
            return redirect()->route('tasks.index')->with('error', 'You do not have permission to create tasks.');
        }

        $users = User::all();
        return view('pages.tasks.add', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'assigned_to' => 'required|exists:users,id',
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
            'status' => $request->status,
            'created_by' => auth()->id()
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    public function show($id)
    {
        $task = Task::findOrFail($id);

        if (Auth::user()->role != 'admin' && $task->assigned_to != Auth::id()) {
            return redirect()->route('tasks.index')->with('error', 'You do not have permission to view this task.');
        }

        return view('pages.tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);

        $users = User::all();

        return view('pages.tasks.edit', compact('task', 'users'));
    }

    public function updateStatus(Request $request, Task $task)
    {
        $this->authorize('updateStatus', $task);

        $validated = $request->validate([
            'status' => 'required|string|max:255',
        ]);

        $task->status = $validated['status'];
        $task->save();

        return redirect()->back()->with('success', 'Task status updated successfully.');
    }

    public function updateAssignee(Request $request, Task $task)
    {
        $this->authorize('updateAssignee', $task);

        $validated = $request->validate([
           'assigned_to' => 'required|exists:users,id',
        ]);

        $task->assigned_to = $validated['assigned_to'];
        $task->save();

        return redirect()->back()->with('success', 'Task assignee updated successfully.');
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'assigned_to' => 'required|exists:users,id',
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $task->update($validated);

        return redirect()->back()->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }
}

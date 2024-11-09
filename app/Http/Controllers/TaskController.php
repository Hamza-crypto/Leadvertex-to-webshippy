<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index()
    {
        // Check if the user is admin or a regular user
        if (Auth::user()->role == 'admin') {
            // Admin can see all tasks
            $tasks = Task::with('assignedUser')->get();

        } else {
            // Regular users can only see tasks assigned to them
            $tasks = Task::where('assigned_to', Auth::id())->get();
        }
        $users = User::all();

        return view('pages.tasks.index', compact('tasks', 'users'));
    }

    // Show form for creating a new task (only for admins)
    public function create()
    {
        // Check if the user is admin
        if (Auth::user()->role != 'admin') {
            return redirect()->route('tasks.index')->with('error', 'You do not have permission to create tasks.');
        }

        // Pass data for user selection (only for admins)
        $users = User::all();
        return view('pages.tasks.add', compact('users'));
    }

    // Store a new task (only for admins)
    public function store(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'assigned_to' => 'required|exists:users,id', // Ensure a valid user is selected
            'status' => 'required|in:pending,in_progress,completed', // Example status
        ]);

        // Create a new task
        Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
            'status' => $request->status,
            'created_by' => auth()->id()
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    // Show task details (view task)
    public function show($id)
    {
        // Find task by ID
        $task = Task::findOrFail($id);

        // Check if the task is assigned to the current user or is an admin
        if (Auth::user()->role != 'admin' && $task->user_id != Auth::id()) {
            return redirect()->route('tasks.index')->with('error', 'You do not have permission to view this task.');
        }

        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        // Ensure only admin or the assigned user can access this edit view
        $this->authorize('update', $task);

        // Get the list of users (admin only functionality)
        $users = User::all();

        return view('pages.tasks.edit', compact('task', 'users'));
    }

    public function updateStatus(Request $request, Task $task)
    {
        // Authorize the action using the policy
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
        // Authorize the action using the policy
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
        // Only allow admins to update the task details
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'assigned_to' => 'required|exists:users,id', // Ensure a valid user is selected
            'status' => 'required|in:pending,in_progress,completed', // Example status
        ]);

        $task->update($validated);

        return redirect()->back()->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        // Only allow admins to delete the task
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }
}

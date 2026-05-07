<?php

namespace App\Domains\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Users\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserService $service
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->service->getAll()]);
    }

    public function show(string $id): JsonResponse
    {
        $entity = $this->service->find($id);
        if (!$entity) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json(['data' => $entity]);
    }

    public function store(Request $request): JsonResponse
    {
        $entity = $this->service->create($request->validated());
        return response()->json(['data' => $entity], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $entity = $this->service->update($id, $request->validated());
        if (!$entity) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json(['data' => $entity]);
    }

    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->service->delete($id);
        return response()->json(['success' => $deleted]);
    }
}
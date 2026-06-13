<?php

namespace App\Http\Controllers;

use App\Actions\User\DestroyUserAction;
use App\Actions\User\PaginateUserAction;
use App\Actions\User\ShowUserAction;
use App\Actions\User\StoreUserAction;
use App\Actions\User\UpdateUserAction;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class UserController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        return $this->response(
            message: __('messages.user.me'),
            data: new UserResource($request->user()),
        );
    }

    public function index(Request $request, PaginateUserAction $action): ResourceCollection
    {
        $result = $action->execute($request);

        return new PaginationResource($result);
    }

    public function store(StoreUserRequest $request, StoreUserAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        return $this->response(
            message: __('messages.user.store'),
            data: new UserResource($result),
            status: HttpResponse::HTTP_CREATED
        );
    }

    public function show(User $user, ShowUserAction $action): JsonResponse
    {
        $result = $action->execute($user);

        return $this->response(
            message: __('messages.user.show'),
            data: new UserResource($result),
        );
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUserAction $action): JsonResponse
    {
        $result = $action->execute($request->validated(), $user);

        return $this->response(
            message: __('messages.user.update'),
            data: new UserResource($result),
        );
    }

    public function destroy(User $user, DestroyUserAction $action): JsonResponse
    {
        $action->execute($user);

        return $this->response(
            message: __('messages.user.destroy'),
            status: HttpResponse::HTTP_NO_CONTENT
        );
    }
}

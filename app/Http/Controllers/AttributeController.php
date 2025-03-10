<?php

namespace App\Http\Controllers;

use App\Actions\Attribute\CreateAttributeAction;
use App\Actions\Attribute\GetAttributesAction;
use App\Actions\Attribute\UpdateAttributeAction;
use App\Http\Requests\AttributeRequest;
use App\Http\Resources\AttributeResource;
use App\Models\Attribute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class AttributeController extends Controller
{
    public function index(
        Request             $request,
        GetAttributesAction $getAttributes,
    ): JsonResponse
    {
        $attributes = $getAttributes->handle();

        return api()
            ->success(AttributeResource::collection($attributes))
            ->respond();
    }

    /**
     * @throws Throwable
     */
    public function store(
        AttributeRequest $request,
        CreateAttributeAction $createAttribute,
    ): JsonResponse
    {
        return DB::transaction(function () use ($createAttribute, $request) {
            $attribute = $createAttribute->handle($request->validated());

            return api()
                ->success(AttributeResource::make($attribute))
                ->respond();
        });
    }

    public function show(
        Attribute $attribute
    ): JsonResponse
    {
        return api()
            ->success(AttributeResource::make($attribute))
            ->respond();
    }

    /**
     * @throws Throwable
     */
    public function update(
        AttributeRequest $request,
        Attribute $attribute,
        UpdateAttributeAction $updateAttribute,
    ): JsonResponse
    {
        return DB::transaction(function () use ($attribute, $updateAttribute, $request) {
            $attribute = $updateAttribute->handle($attribute, $request->validated());

            return api()
                ->success(AttributeResource::make($attribute))
                ->respond();
        });
    }

    /**
     * @throws Throwable
     */
    public function destroy(
        Attribute $attribute
    ): JsonResponse
    {
        return DB::transaction(function () use ($attribute) {
            $attribute->delete();

            return api()
                ->success()
                ->respond();
        });
    }
}

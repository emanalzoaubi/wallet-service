<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;

use App\Http\Requests\{
    CreateWalletRequest,
    GetWalletsRequest
};
use App\Http\Resources\{
    BalanceResource,
    WalletCollection,
    WalletResource
};
use Illuminate\Http\Response;

class WalletController extends BaseController
{
    private $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function index(GetWalletsRequest $request): JsonResponse
    {
        $perPage = $request->get('per_page', config('pagination.per_page'));
        $filters = $request->only(['owner_name', 'currency']);

        $wallets = $this->walletService->getAllWallets((int) $perPage, $filters);
        return $this->respond(new WalletCollection($wallets));
    }

    public function store(CreateWalletRequest $request): JsonResponse
    {
        $wallet = $this->walletService->createWallet($request->owner_name, $request->currency);
        return $this->respond(new WalletResource($wallet), Response::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $wallet = $this->walletService->getWalletById($id);
        return $this->respond(new WalletResource($wallet));
    }

    public function showBalance(int $id): JsonResponse
    {
        $wallet = $this->walletService->getWalletBalance($id);

        return $this->respond(new BalanceResource($wallet));
    }
}

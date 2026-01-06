<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\WalletService;
use Illuminate\Http\{
    JsonResponse,
    Response
};
use App\Http\Requests\{
    CreateWalletRequest,
    GetWalletsRequest
};
use App\Http\Resources\{
    BalanceResource,
    WalletCollection,
    WalletResource
};

class WalletController extends BaseController
{

    public function __construct(private WalletService $walletService) {}

    /**
     * Get all wallets
     * 
     * @param GetWalletsRequest $request
     * @return JsonResponse
     */
    public function index(GetWalletsRequest $request): JsonResponse
    {
        $perPage = $request->get('per_page', config('pagination.per_page'));
        $filters = $request->only(['owner_name', 'currency']);

        $wallets = $this->walletService->getAllWallets((int) $perPage, $filters);
        return $this->respond(new WalletCollection($wallets));
    }

    /**
     * Create a new wallet
     * 
     * @param CreateWalletRequest $request
     * @return JsonResponse
     */
    public function store(CreateWalletRequest $request): JsonResponse
    {
        $wallet = $this->walletService->createWallet($request->owner_name, $request->currency);
        return $this->respond(new WalletResource($wallet), Response::HTTP_CREATED);
    }

    /**
     * Get a wallet by ID
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $wallet = $this->walletService->getWalletById($id);
        return $this->respond(new WalletResource($wallet));
    }

    /**
     * Get the balance of a wallet
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function showBalance(int $id): JsonResponse
    {
        $wallet = $this->walletService->getWalletBalance($id);

        return $this->respond(new BalanceResource($wallet));
    }
}

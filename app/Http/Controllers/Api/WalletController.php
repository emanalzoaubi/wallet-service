<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\CreateWalletRequest;
use App\Services\WalletService;
use App\Http\Resources\{
    BalanceResource,
    WalletCollection,
    WalletResource
};
use Illuminate\Http\Request;

class WalletController extends BaseController
{
    private $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', config('pagination.per_page'));
        $filters = $request->only(['owner_name', 'currency']);

        $wallets = $this->walletService->getAllWallets((int) $perPage, $filters);
        return $this->respond(new WalletCollection($wallets));
    }

    public function store(CreateWalletRequest $request)
    {
        $wallet = $this->walletService->createWallet($request->owner_name, $request->currency);
        return $this->respond($wallet);
    }

    public function show(int $id)
    {
        $wallet = $this->walletService->getWalletById($id);
        return $this->respond(new WalletResource($wallet));
    }

    public function showBalance(int $id)
    {
        $wallet = $this->walletService->getWalletBalance($id);

        return $this->respond(new BalanceResource($wallet));
    }
}

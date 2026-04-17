<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Client as GoogleClient;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class GoogleAuthController extends Controller
{
    private function getClient()
    {
        $clientId = config('settings.google_client_id');
        $clientSecret = config('settings.google_client_secret');

        if (!$clientId || !$clientSecret) {
            abort(403, 'A configuração base do Client ID e Secret do Google Drive estão ausentes no sistema.');
        }

        $client = new GoogleClient();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri(route('admin.settings.google.callback'));
        $client->setAccessType('offline'); 
        $client->setPrompt('consent'); // Forçar Refresh Token na aprovação
        $client->addScope('https://www.googleapis.com/auth/drive');

        return $client;
    }

    public function redirectToGoogle()
    {
        $client = $this->getClient();
        $authUrl = $client->createAuthUrl();

        return redirect()->away($authUrl);
    }

    public function handleGoogleCallback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('admin.settings.index')->with('error', 'Autenticação com Google foi recusada pela provedora ou por você.');
        }

        if (!$request->has('code')) {
            return redirect()->route('admin.settings.index')->with('error', 'Código de autenticação final Ausente.');
        }

        try {
            $client = $this->getClient();
            // Troca o código pelo bundle durável que contém o Refresh Token
            $tokenBundle = $client->fetchAccessTokenWithAuthCode($request->code);

            if (isset($tokenBundle['error'])) {
                throw new \Exception(json_encode($tokenBundle));
            }

            if (isset($tokenBundle['refresh_token'])) {
                // Atualiza o banco de dados global com a chave inestimável invisível
                Setting::updateOrCreate(
                    ['key' => 'google_refresh_token'],
                    ['value' => $tokenBundle['refresh_token']]
                );

                return redirect()->route('admin.settings.index')
                                 ->with('success', 'Integração OAuth de Nuvem executada com sucesso! A máquina já está conectada à sua conta de Storage e o Refresh Token nativo emitido perpetuamente.');
            }

            return redirect()->route('admin.settings.index')->with('warning', 'Autenticado, mas o Google não devolveu o "Refresh Token". Verifique se o aplicativo foi corretamente expurgado nas Conexões da Conta Google para forçar Nova Autorização Profunda.');

        } catch (\Exception $e) {
            Log::error('Erro OAuth 2.0 no Drive: ' . $e->getMessage());
            return redirect()->route('admin.settings.index')->with('error', 'Falha Sistêmica na Transação OAuth: ' . $e->getMessage());
        }
    }
}

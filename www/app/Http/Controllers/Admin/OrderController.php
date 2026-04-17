<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Yajra\DataTables\DataTables;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::with(['user', 'package', 'gallery'])->select('orders.*');
            return Datatables::of($data)
                    ->addColumn('client_name', function($row){
                         return $row->user->name;
                    })
                    ->addColumn('package_name', function($row){
                         return $row->package->name ?? 'Avulso';
                    })
                    ->editColumn('total_amount', function($row){
                         return 'R$ ' . number_format($row->total_amount, 2, ',', '.');
                    })
                    ->editColumn('status', function($row) {
                         if($row->status == 'paid') return '<span class="badge bg-success">Pago</span>';
                         if($row->status == 'cancelled') return '<span class="badge bg-danger">Cancelado</span>';
                         return '<span class="badge bg-warning text-dark">Pendente</span>';
                    })
                    ->addColumn('action', function($row){
                         $btn = '<a href="'.route('admin.orders.show', $row->id).'" class="btn btn-info btn-sm me-1" title="Ver Seleção do Cliente"><i class="bi bi-card-image"></i> Ver Fotos</a>';
                         
                         if($row->status == 'pending') {
                             $btn .= '<form action="'.route('admin.orders.update', $row->id).'" method="POST" class="d-inline">
                                      '.csrf_field().method_field('PATCH').'
                                      <input type="hidden" name="status" value="paid">
                                      <button type="submit" class="btn btn-success btn-sm me-1" title="Aprovar Pagamento"><i class="bi bi-check-circle"></i> Aprovar</button>
                                      </form>';
                         } else if ($row->status == 'paid') {
                             $btn .= '<form action="'.route('admin.orders.update', $row->id).'" method="POST" class="d-inline">
                                      '.csrf_field().method_field('PATCH').'
                                      <input type="hidden" name="status" value="pending">
                                      <button type="submit" class="btn btn-warning btn-sm me-1" title="Estornar para Pendente (Manual)"><i class="bi bi-arrow-counterclockwise"></i></button>
                                      </form>';
                             $btn .= '<form action="'.route('admin.orders.update', $row->id).'" method="POST" class="d-inline" onsubmit="return confirm(\'Isso acionará estorno no Gateway se aplicável (Ex: PIX Asaas). Continuar?\');">
                                      '.csrf_field().method_field('PATCH').'
                                      <input type="hidden" name="status" value="cancelled">
                                      <button type="submit" class="btn btn-danger btn-sm me-1" title="Cancelar Venda e Estornar"><i class="bi bi-x-octagon"></i></button>
                                      </form>';
                         }
                         
                         $btn .= '<form action="'.route('admin.orders.destroy', $row->id).'" method="POST" class="d-inline" onsubmit="return confirm(\'Deseja excluir esta fatura?\');">
                                  '.csrf_field().method_field('DELETE').'
                                  <button type="submit" class="btn btn-danger btn-sm" title="Excluir"><i class="bi bi-trash"></i></button>
                                  </form>';

                         return $btn;
                    })
                    ->rawColumns(['status', 'action'])
                    ->make(true);
        }
        
        return view('admin.orders.index');
    }

    public function show($id)
    {
        $order = Order::with(['items.photo', 'package', 'gallery', 'user'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,paid,cancelled'
        ]);

        if ($validated['status'] == 'cancelled' && $order->status == 'paid') {
            // Estorno Financeiro Real
            try {
                 $methodEnum = \App\Enums\PaymentMethodEnum::tryFrom($order->gateway);
                 if ($methodEnum) {
                     $gateway = \App\Services\Payments\PaymentGatewayFactory::resolve($methodEnum);
                     // Dispara instrução para a operadora (Ex: Asaas, Stripe)
                     $gateway->refundCharge($order);
                 }
            } catch (\Exception $e) {
                 \Illuminate\Support\Facades\Log::error('Erro ao estornar fatura pelo painel: ' . $e->getMessage());
            }
        }

        $order->update([
            'status' => $validated['status'],
            'paid_at' => $validated['status'] == 'paid' ? now() : null
        ]);
        
        return redirect()->route('admin.orders.index')->with('success', 'Status da Fatura modificado com sucesso!');
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('admin.orders.index')->with('success', 'Fatura excluída permanentemente.');
    }
}

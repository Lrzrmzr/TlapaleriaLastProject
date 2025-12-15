<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;

class PublicController extends Controller
{
    public function index()
    {
        $products = $this->getPublicProducts();

        return Inertia::render('Public/Home', [
            'products' => $products
        ]);
    }

    /**
     * API endpoint público para consulta de productos
     * No requiere autenticación
     * Retorna solo información básica sin historial
     */
    public function apiProducts()
    {
        $products = $this->getPublicProducts();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Obtiene la lista de productos con información básica
     */
    private function getPublicProducts()
    {
        return Product::with(['suppliers:id,name'])
            ->select(['id', 'name', 'description'])
            ->get()
            ->map(function ($product) {
                $category = $this->determineCategory($product->name . ' ' . ($product->description ?? ''));

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'category' => $category,
                    'suppliers' => $product->suppliers->map(fn($supplier) => [
                        'id' => $supplier->id,
                        'name' => $supplier->name
                    ])
                ];
            });
    }

    private function determineCategory($text)
    {
        $text = strtolower($text);

        $categories = [
            'Herramientas' => ['herramienta', 'martillo', 'destornillador', 'llave', 'alicate', 'pinza', 'sierra', 'taladro'],
            'Construcción' => ['cemento', 'ladrillo', 'arena', 'grava', 'block', 'varilla', 'concreto', 'cal'],
            'Electricidad' => ['cable', 'electricidad', 'foco', 'lámpara', 'interruptor', 'contacto', 'voltaje'],
            'Plomería' => ['tubo', 'tubería', 'llave', 'válvula', 'agua', 'drenaje', 'conexión', 'codo'],
            'Pintura' => ['pintura', 'brocha', 'rodillo', 'barniz', 'esmalte', 'thinner', 'color'],
        ];

        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    return $category;
                }
            }
        }

        return 'General';
    }
}

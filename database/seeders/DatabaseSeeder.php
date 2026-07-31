<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use App\Models\Block;
use App\Models\PipeCategory;
use App\Models\PipeSize;
use App\Models\PipeType;
use App\Models\PipeClass;
use App\Models\PipeWeight;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Seed 3 Petugas Users (Akbar, Reo, Dendi)
        User::updateOrCreate(
            ['email' => 'akbar@spindo.com'],
            ['name' => 'Akbar', 'password' => 'akbar1122']
        );

        User::updateOrCreate(
            ['email' => 'reo@spindo.com'],
            ['name' => 'Reo', 'password' => 'reo1122']
        );

        User::updateOrCreate(
            ['email' => 'dendi@spindo.com'],
            ['name' => 'Dendi', 'password' => 'Dendiaprilio1204']
        );

        // 1. Seed Warehouses & 36 Blocks each (A1-L3)
        $warehouses = [
            ['name' => 'Gudang 1', 'description' => 'Area Utama - Unit 7 Gresik'],
            ['name' => 'Gudang 2', 'description' => 'Area Timur - Unit 7 Gresik'],
            ['name' => 'Gudang 3', 'description' => 'Area Barat - Unit 7 Gresik'],
        ];

        $letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];

        foreach ($warehouses as $index => $whData) {
            $wh = Warehouse::firstOrCreate(['name' => $whData['name']], $whData);

            foreach ($letters as $letter) {
                for ($num = 1; $num <= 3; $num++) {
                    $code = "{$letter}{$num}";
                    $sloc = "7A" . strtoupper($letter) . $num;
                    Block::firstOrCreate(
                        ['warehouse_id' => $wh->id, 'code' => $code],
                        ['sloc_code' => $sloc]
                    );
                }
            }
        }

        // 2. Seed Pipe Categories
        $categories = [
            ['code' => 'hitam',    'name' => 'PIPA HITAM'],
            ['code' => 'galvanis', 'name' => 'PIPA GALVANIS'],
            ['code' => 'kotak',    'name' => 'PIPA KOTAK / HOLLOW'],
        ];
        foreach ($categories as $cat) {
            PipeCategory::firstOrCreate(['code' => $cat['code']], $cat);
        }

        // 3. Seed Pipe Sizes
        $sizes = [
            ['size_label' => '1/2"',   'pcs_per_bundle' => 217],
            ['size_label' => '3/4"',   'pcs_per_bundle' => 169],
            ['size_label' => '1"',     'pcs_per_bundle' => 127],
            ['size_label' => '1 1/4"', 'pcs_per_bundle' => 91],
            ['size_label' => '1 1/2"', 'pcs_per_bundle' => 61],
            ['size_label' => '2"',     'pcs_per_bundle' => 61],
            ['size_label' => '2 1/2"', 'pcs_per_bundle' => 37],
            ['size_label' => '3"',     'pcs_per_bundle' => 29],
            ['size_label' => '4"',     'pcs_per_bundle' => 19],
            ['size_label' => '5"',     'pcs_per_bundle' => 14],
            ['size_label' => '6"',     'pcs_per_bundle' => 10],
            ['size_label' => '8"',     'pcs_per_bundle' => 7],
        ];

        $sizeModels = [];
        foreach ($sizes as $s) {
            $sizeModels[$s['size_label']] = PipeSize::firstOrCreate(
                ['size_label' => $s['size_label']],
                ['pcs_per_bundle' => $s['pcs_per_bundle']]
            );
        }

        // 4. Seed Pipe Types (Grade) — G-A dan G-B
        $types = [
            ['code' => 'G-A', 'name' => 'Grade A'],
            ['code' => 'G-B', 'name' => 'Grade B'],
        ];

        $typeModels = [];
        foreach ($types as $t) {
            $typeModels[$t['code']] = PipeType::firstOrCreate(['code' => $t['code']], $t);
        }

        // 5. Seed Pipe Classes
        $classes = [
            ['code' => 'SCH40', 'name' => 'SCH 40'],
            ['code' => 'L',     'name' => 'L'],
            ['code' => 'M',     'name' => 'M'],
            ['code' => 'BSA',   'name' => 'BSA'],
            ['code' => 'MED',   'name' => 'MED'],
        ];
        foreach ($classes as $cl) {
            PipeClass::firstOrCreate(['code' => $cl['code']], $cl);
        }

        // 6. Seed Pipe Weights (Grade A & B per bundle in KG)
        $weights = [
            // size_label => [G-A, G-B] weight per bundle
            '1/2"'   => ['G-A' => 1100.00, 'G-B' => 1391.00],
            '3/4"'   => ['G-A' => 1150.00, 'G-B' => 1402.70],
            '1"'     => ['G-A' => 1200.00, 'G-B' => 1500.00],
            '1 1/4"' => ['G-A' => 1300.00, 'G-B' => 1437.80],
            '1 1/2"' => ['G-A' => 1100.00, 'G-B' => 1128.50],
            '2"'     => ['G-A' => 1200.00, 'G-B' => 1391.42],
            '2 1/2"' => ['G-A' => 1100.00, 'G-B' => 1200.00],
            '3"'     => ['G-A' => 1150.00, 'G-B' => 1250.00],
            '4"'     => ['G-A' => 1100.00, 'G-B' => 1180.00],
            '5"'     => ['G-A' => 1100.00, 'G-B' => 1220.00],
            '6"'     => ['G-A' => 1050.00, 'G-B' => 1150.00],
            '8"'     => ['G-A' => 1100.00, 'G-B' => 1250.00],
        ];

        foreach ($weights as $sizeLabel => $typeWeights) {
            if (isset($sizeModels[$sizeLabel])) {
                $sizeObj = $sizeModels[$sizeLabel];
                foreach ($typeWeights as $typeCode => $wKg) {
                    if (isset($typeModels[$typeCode])) {
                        PipeWeight::firstOrCreate(
                            ['pipe_size_id' => $sizeObj->id, 'pipe_type_id' => $typeModels[$typeCode]->id],
                            ['weight_per_bundle' => $wKg]
                        );
                    }
                }
            }
        }
    }
}

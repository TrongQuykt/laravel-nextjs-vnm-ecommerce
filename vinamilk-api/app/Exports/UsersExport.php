<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Database\Eloquent\Builder;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->query->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Tên',
            'Email',
            'Số điện thoại',
            'Vai trò',
            'Điểm thưởng',
            'Email xác nhận',
            'Ngày tạo',
        ];
    }

    /**
     * @param User $user
     * @return array
     */
    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->phone ?? 'N/A',
            $user->role ?? 'user',
            $user->reward_points,
            $user->email_verified_at ? 'Đã xác nhận' : 'Chưa xác nhận',
            $user->created_at->format('d/m/Y H:i'),
        ];
    }
}

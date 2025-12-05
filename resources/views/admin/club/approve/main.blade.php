<x-dash.admin.layout>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <table class="table table-bordered">
        <thead>
            <tr>
                <th scope="col">รหัสการสมัคร</th>
                <th scope="col">ชื่อ-นามสกุล ผู้สมัคร</th>
                <th scope="col">ความสามารถ</th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
                @foreach ($clubApprovals as $approval)
                <tr>
                    <td> {{ $approval->member_id }} </td>
                    <td> {{ $approval->user->name }} {{ $approval->user->surname }} </td>
                    <td> {{ $approval->ability }} </td>
                    <td>
                        <a href=" {{ route('admin.club.approve.detail', ['id' => $approval->member_id]) }}" class="btn btn-primary btn-sm">ดูรายละเอียด</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-dash.admin.layout>
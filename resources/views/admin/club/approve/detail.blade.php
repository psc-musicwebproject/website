<x-dash.admin.layout>
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    <table class="table table-bordered table-striped">
        <tbody>
            @foreach ($clubMember as $approval)
                <tr>
                    <td>รหัสการสมัคร</td>
                    <td> {{ $approval->member_id }} </td>
                </tr>
                <tr>
                    <td>ชื่อผู้สมัคร</td>
                    <td> {{ $approval->user->name }} {{ $approval->user->surname }} </td>
                </tr>
                <tr>
                    <td>เวลาที่สมัคร</td>
                    <td> {{ $approval->created_at }} </td>
                </tr>
                <tr>
                    <td>ความสามารถ</td>
                    <td> {{ $approval->ability }} </td>
                </tr>
            @endforeach
        </thead>
    </table>
        <form action="{{ route('admin.club.approve.update', $clubMember->first()->member_id) }}" method="POST">
            @csrf
            <div class="card">
                <div class="card-header">
                    <span>อนุมัติ / ไม่อนุมัติการสมัคร</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="approve_reason" class="form-label">เหตุผล (ถ้ามี)</label>
                        <input type="text" class="form-control" placeholder="สาเหตุการตัดสิน" name="approve_reason">
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-end">
                        <button type="submit" name="action" value="approve" class="btn btn-success me-2">
                            <i class="bi bi-file-earmark-check-fill"></i>
                            <span>อนุมัติ</span>
                        </button>
                        <button type="submit" name="action" value="reject" class="btn btn-danger">
                            <i class="bi bi-file-earmark-excel-fill"></i>
                            <span>ไม่อนุมัติ</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
</x-dash.admin.layout>
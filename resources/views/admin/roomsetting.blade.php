@use('App\Models\Room')

@extends('layouts.admin')

@section('content')
    <!-- Rooms List -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th scope="col">ชื่อห้อง</th>
                <th scope="col">สถานะ</th>
                <th scope="col">
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addroom">เพิ่มห้อง</button>
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach (Room::all() as $room)
            <tr>
                <td> {{ $room->room_name }} </td>
                <td class="text-center">
                    @if ($room->room_status == 'available')
                    <span class="badge bg-success">ว่าง</span>
                    @elseif ($room->room_status == 'in_use')
                    <span class="badge bg-danger">กำลังใช้งาน</span>
                    @elseif ($room->room_status == 'disabled')
                    <span class="badge bg-secondary">ไม่พร้อมใช้งาน</span>
                    @else
                    <span class="badge bg-warning">เกิดข้อผิดพลาด</span>
                    @endif
                </td>
                <td>
                    <div class="d-grid gap-2 d-md-flex">
                        <button class="btn btn-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#editroom-{{ $room->room_id }}">แก้ไข</button>
                        <button class="btn btn-danger text-nowrap" data-bs-toggle="modal" data-bs-target="#deleteroom-{{ $room->room_id }}">ลบ</button>
                        @if ($room->room_status != 'disabled' && $room->room_status != 'in_use')
                        <button class="btn btn-secondary text-nowrap" data-bs-toggle="modal" data-bs-target="#disableroom-{{ $room->room_id }}">ปิดใช้งาน</button>
                        @elseif ($room->room_status == 'disabled')
                        <button class="btn btn-success text-nowrap" data-bs-toggle="modal" data-bs-target="#enableroom-{{ $room->room_id }}">เปิดใช้งาน</button>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Add Room Modal -->
    <div class="modal fade" id="addroom" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">เพิ่มห้อง</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action=" {{ route('admin.room.add') }} ">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">ชื่อห้อง</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">เพิ่ม</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Room Modal -->
    @foreach (Room::all() as $room)
    <div class="modal fade" id="editroom-{{ $room->room_id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">แก้ไขห้อง</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.room.edit', ['room_id' => $room->room_id]) }} ">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">ชื่อห้อง</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $room->room_name }}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    <!-- Delete Room Modal -->
    @foreach (Room::all() as $room)
    <div class="modal fade" id="deleteroom-{{ $room->room_id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">ลบห้อง</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action=" {{ route('admin.room.delete', ['room_id' => $room->room_id]) }} ">
                    @csrf
                    <div class="modal-body">
                        <p>คุณแน่ใจหรือไม่ว่าต้องการลบ{{ $room->room_name }} ?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-danger">ลบ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    <!-- Disable Confirmation Modal -->
    @foreach (Room::all() as $room)
    <div class="modal fade" id="disableroom-{{ $room->room_id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">ปิดใช้งานห้อง</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action=" {{ route('admin.room.disable', ['room_id' => $room->room_id]) }} ">
                    @csrf
                    <div class="modal-body">
                        <p>คุณแน่ใจหรือไม่ว่าต้องการปิดใช้งาน{{ $room->room_name }} ?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-warning">ปิดใช้งาน</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    <!-- Enable Confirmation Modal -->
    @foreach (Room::all() as $room)
    <div class="modal fade" id="enableroom-{{ $room->room_id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">เปิดใช้งานห้อง</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action=" {{ route('admin.room.enable', ['room_id' => $room->room_id]) }} ">
                    @csrf
                    <div class="modal-body">
                        <p>คุณแน่ใจหรือไม่ว่าต้องการเปิดใช้งาน{{ $room->room_name }} ?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-success">เปิดใช้งาน</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
@endsection

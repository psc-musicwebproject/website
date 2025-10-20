<x-dash.layout>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">แบบฟอร์มจองห้อง</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-warning">
                <h4><i class="bi bi-exclamation-triangle"></i> ฟีเจอร์นี้อยู่ในระหว่างการพัฒนา</h4>
                <p class="mb-0">ขออภัยในความไม่สะดวก ฟีเจอร์ผู้เข้าร่วมไม่พร้อมใช้งานในขณะนี้ กรุณาติดต่อผู้ดูแลระบบสำหรับข้อมูลเพิ่มเติม</p>
            </div>
            <form>
                @csrf
                <div class="container">
                    <div class="row">
                        <div class="col">
                                <div class="mb-3">
                                    <label for="room_id" class="form-label">ห้องที่ต้องการจอง</label>
                                    <select class="form-select" id="room_id" name="room_id" required @if(count($rooms) == 0 || count($rooms) == 1) disabled @endif>
                                        @foreach($rooms as $room)
                                            <option value="{{ $room->room_id }}">{{ $room->room_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">ระยะเวลาการจอง</label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label small">วันที่</label>
                                            <input type="date" class="form-control" id="date" placeholder="วันที่">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">เวลาเริ่ม</label>
                                            <input type="time" class="form-control" id="time_from" placeholder="เวลาเริ่ม">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">เวลาสิ้นสุด</label>
                                            <input type="time" class="form-control" id="time_to" placeholder="เวลาสิ้นสุด">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label">ชื่อการจอง</label>
                                    <input type="text" class="form-control" id="name" placeholder="หัวข้อการจอง / ใช้ห้อง">
                                </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">ส่งแบบฟอร์ม</button>
            </div>
        </form>
    </div>
</x-dash.layout>
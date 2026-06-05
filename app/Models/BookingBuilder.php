<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class BookingBuilder {

    private array $data = [];

    public function setUserId(?int $userId): self        { $this->data['user_id']        = $userId; return $this; }
    public function setCustomerName(string $name): self  { $this->data['customer_name']  = $name;   return $this; }
    public function setCustomerEmail(string $email): self{ $this->data['customer_email'] = $email;  return $this; }
    public function setCustomerPhone(string $phone): self{ $this->data['customer_phone'] = $phone;  return $this; }
    public function setCheckIn(string $date): self       { $this->data['check_in']       = $date;   return $this; }
    public function setCheckOut(string $date): self      { $this->data['check_out']      = $date;   return $this; }
    public function setPeople(int $count): self          { $this->data['people']         = $count;  return $this; }
    public function setRoomId(int $roomId): self         { $this->data['room_id']        = $roomId; return $this; }
    public function setPaymentMethod(string $m): self    { $this->data['payment_method'] = $m;      return $this; }

    public function build(): array {
        $required = ['customer_name', 'customer_email', 'customer_phone',
                     'check_in', 'check_out', 'people', 'room_id'];
        foreach ($required as $field) {
            if (empty($this->data[$field])) {
                throw new \InvalidArgumentException("Thiếu trường bắt buộc: $field");
            }
        }

        $ci = new \DateTime($this->data['check_in']);
        $co = new \DateTime($this->data['check_out']);
        if ($co <= $ci) {
            throw new \InvalidArgumentException('Ngày trả phòng phải sau ngày nhận phòng.');
        }

        $this->data['nights'] = (int)$ci->diff($co)->days;
        return $this->data;
    }
}


// ─────────────────────────────────────────────────
// BookingService
// ─────────────────────────────────────────────────



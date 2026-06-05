<?php

namespace App\Http\Controllers;

class Roomcontroller extends Controller {

    private \App\Models\Room   $roomModel;
    private \App\Models\Review $reviewModel;

    public function __construct() {
        $this->roomModel   = new \App\Models\Room();
        $this->reviewModel = new \App\Models\Review();
    }

    public function index(): void {
        $rooms = $this->roomModel->all();
        $this->render('room/index', [
            'rooms' => $rooms,
            'flash' => $this->getFlash(),
        ]);
    }

    /**
     * GET ?controller=room&action=detail&id={room_types.id}
     *
     * Biến truyền cho view detail.php:
     *   $room           → loại phòng (type_name, price, max_guests, description, amenities[])
     *   $allRoomsOfType → phòng vật lý (id, room_number, floor, is_booked)
     *   $bookedRoomIds  → mảng id phòng đã đặt
     */
    public function detail(): void {
        $typeId   = (int)($_GET['id'] ?? $_GET['type_id'] ?? 0);
        $checkIn  = trim($_GET['check_in']  ?? '');
        $checkOut = trim($_GET['check_out'] ?? '');
        $adults   = max(1, (int)($_GET['adults'] ?? ($_GET['people'] ?? 1)));
        $children = max(0, (int)($_GET['children'] ?? 0));
        $people   = $adults + $children;

        $room = $this->roomModel->findTypeById($typeId);

        if (!$room) {
            $this->setFlash('error', 'Loại phòng không tồn tại.');
            $this->redirectToAction('home');
            return;
        }

        $allRoomsOfType = $this->roomModel->getRoomsByType($typeId, $checkIn, $checkOut);

        $bookedRoomIds = array_column(
            array_filter($allRoomsOfType, fn($r) => $r['is_booked']),
            'id'
        );

        $availableCount = count($allRoomsOfType) - count($bookedRoomIds);

        // Lấy danh sách đánh giá & thông tin sao trung bình
        $reviews = $this->reviewModel->findByRoomType($typeId);
        $ratingData = $this->reviewModel->getAverageRating($typeId);

        $this->render('room/detail', [
            'room'           => $room,
            'allRoomsOfType' => $allRoomsOfType,
            'bookedRoomIds'  => $bookedRoomIds,
            'availableCount' => $availableCount,
            'checkIn'        => $checkIn,
            'checkOut'       => $checkOut,
            'people'         => $people,
            'adults'         => $adults,
            'children'       => $children,
            'reviews'        => $reviews,
            'avgRating'      => $ratingData['avg_rating'],
            'countReviews'   => $ratingData['count_reviews'],
            'flash'          => $this->getFlash(),
        ]);
    }

    /**
     * GET ?controller=room&action=search
     */
    public function search(): void {
        $checkIn  = trim($_GET['check_in']  ?? '');
        $checkOut = trim($_GET['check_out'] ?? '');
        $adults   = max(1, (int)($_GET['adults'] ?? ($_GET['people'] ?? 1)));
        $children = max(0, (int)($_GET['children'] ?? 0));
        $people   = $adults + $children;

        $roomTypes    = [];
        $allAmenities = $this->roomModel->getAllAmenities();
        $searched     = false;
        $totalAvailable = 0;

        if ($checkIn && $checkOut) {
            $searched  = true;
            $roomTypes = $this->roomModel->searchAvailableGroupedByType($checkIn, $checkOut, $people);
            foreach ($roomTypes as $rt) {
                $totalAvailable += count($rt['available_rooms']);
            }
        }

        $this->render('room/search', [
            'roomTypes'      => $roomTypes,
            'allAmenities'   => $allAmenities,
            'checkIn'        => $checkIn,
            'checkOut'       => $checkOut,
            'people'         => $people,
            'adults'         => $adults,
            'children'       => $children,
            'searched'       => $searched,
            'totalAvailable' => $totalAvailable,
            'flash'        => $this->getFlash(),
        ]);
    }

    public function amenities(): void {
        $roomTypes = $this->roomModel->allTypes();
        foreach ($roomTypes as &$type) {
            $type['amenities'] = $this->roomModel->getAmenitiesByType($type['id']);
        }
        $this->render('room/amenities', [
            'roomTypes' => $roomTypes,
            'flash'     => $this->getFlash(),
        ]);
    }
}


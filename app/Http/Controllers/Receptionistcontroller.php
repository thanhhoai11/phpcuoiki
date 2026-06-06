<?php

namespace App\Http\Controllers;

class Receptionistcontroller extends Controller {

    public function dashboard(): void {
        $this->requireRole('receptionist');
        $this->render('receptionist/dashboard', [], 'main');
    }
}

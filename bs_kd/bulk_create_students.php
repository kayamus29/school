<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__."/bootstrap/app.php";
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Repositories\UserRepository;
use App\Repositories\PromotionRepository;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Models\StudentFee;
use App\Models\StudentPayment;
use App\Models\WalletTransaction;
use App\Models\Mark;
use App\Models\Promotion;
use App\Models\StudentParentInfo;
use App\Models\StudentAcademicInfo;
use App\Models\Wallet;

// --- DATA ---

$classMap = [
    'K.G 1' => 2, 'KG II' => 3, 'Nursery 1' => 4, 'Nursery One' => 4,
    'Nursery 2' => 5, 'Nursery Two' => 5, 'Primary 1' => 6, 'Primary One' => 6,
    'Primary 2' => 7, 'Primary Two' => 7, 'Primary 3' => 8, 'Primary Three' => 8,
    'Primary 4' => 9, // Maps to the base ID for Primary 4
    'Primary 5' => 11, 'Primary Five' => 11, 'JSS 1' => 12, 'JSS 2' => 13,
    'JSS 3' => 14, 'SS 1' => 15, 'SS One' => 15, 'SS 2' => 16, 'SS Two' => 16,
    'SS 3' => 17, 'SS Three' => 17,
];

$sectionMap = [
    2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8,
    9 => ['A' => 9, 'B' => 10], // Primary 4
    11 => 11, 12 => 12, 13 => 13, 14 => 14,
    15 => ['A' => 15, 'B' => 16], // SS 1
    16 => ['A' => 18, 'B' => 17], // SS 2
    17 => ['A' => 19, 'B' => 20], // SS 3
];

$newStudentsData = [
    ['sn' => 1, 'full_name' => 'Abdulrazak Abduljabar', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'a.abduljabar@abuja.bestsolution.ng'],
    ['sn' => 2, 'full_name' => 'Adegoke Goodnews', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'a.goodnews@abuja.bestsolution.ng'],
    ['sn' => 3, 'full_name' => 'Adekunle Victory', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'a.victory@abuja.bestsolution.ng'],
    ['sn' => 4, 'full_name' => 'Akawa Gianna', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'a.gianna@abuja.bestsolution.ng'],
    ['sn' => 5, 'full_name' => 'Amodu Abdullahi', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'a.abdullahi@abuja.bestsolution.ng'],
    ['sn' => 6, 'full_name' => 'Bashir Aisha', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'b.aisha@abuja.bestsolution.ng'],
    ['sn' => 7, 'full_name' => 'Christopher Chikamso', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'c.chikamso@abuja.bestsolution.ng'],
    ['sn' => 8, 'full_name' => 'Christopher Daniel', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'c.daniel@abuja.bestsolution.ng'],
    ['sn' => 9, 'full_name' => 'Chukwudi Daniel', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'c.daniel2@abuja.bestsolution.ng'],
    ['sn' => 10, 'full_name' => 'Edwin Prevail', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'e.prevail@abuja.bestsolution.ng'],
    ['sn' => 11, 'full_name' => 'Fatoyinbo Tiwalola', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'f.tiwalola@abuja.bestsolution.ng'],
    ['sn' => 12, 'full_name' => 'Godspower Excel', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'g.excel@abuja.bestsolution.ng'],
    ['sn' => 13, 'full_name' => 'Godwin Jordan', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'g.jordan@abuja.bestsolution.ng'],
    ['sn' => 14, 'full_name' => 'Gamde Zion', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'g.zion@abuja.bestsolution.ng'],
    ['sn' => 15, 'full_name' => 'Harry Wisdom', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'h.wisdom@abuja.bestsolution.ng'],
    ['sn' => 16, 'full_name' => 'Habib Khalifa', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'h.khalifa@abuja.bestsolution.ng'],
    ['sn' => 17, 'full_name' => 'Ikpea Iredia', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'i.iredia@abuja.bestsolution.ng'],
    ['sn' => 18, 'full_name' => 'James David', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'j.david@abuja.bestsolution.ng'],
    ['sn' => 19, 'full_name' => 'Liveth David', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'l.david@abuja.bestsolution.ng'],
    ['sn' => 20, 'full_name' => 'Muhammed Abubakar', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'm.abubakar@abuja.bestsolution.ng'],
    ['sn' => 21, 'full_name' => 'Noah Light', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'n.light@abuja.bestsolution.ng'],
    ['sn' => 22, 'full_name' => 'Nnamdi Victory', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'n.victory@abuja.bestsolution.ng'],
    ['sn' => 23, 'full_name' => 'Onwuzuruike Aziel', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'o.aziel@abuja.bestsolution.ng'],
    ['sn' => 24, 'full_name' => 'Oussou Mirabel', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'o.mirabel@abuja.bestsolution.ng'],
    ['sn' => 25, 'full_name' => 'Peter Goodness', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'p.goodness@abuja.bestsolution.ng'],
    ['sn' => 26, 'full_name' => 'Peter Miracle', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'p.miracle@abuja.bestsolution.ng'],
    ['sn' => 27, 'full_name' => 'Saint-Paul David', 'class' => 'K.G 1', 'section' => 'A', 'email' => 's.david@abuja.bestsolution.ng'],
    ['sn' => 28, 'full_name' => 'Tanimu Hanatu', 'class' => 'K.G 1', 'section' => 'A', 'email' => 't.hanatu@abuja.bestsolution.ng'],
    ['sn' => 29, 'full_name' => 'Williams Retmun', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'w.retmun@abuja.bestsolution.ng'],
    ['sn' => 30, 'full_name' => 'Wenon Testimony', 'class' => 'K.G 1', 'section' => 'A', 'email' => 'w.testimony@abuja.bestsolution.ng'],
    ['sn' => 31, 'full_name' => 'Abdulrahman Muridiyah', 'class' => 'KG II', 'section' => 'A', 'email' => 'a.muridiyah@abuja.bestsolution.ng'],
    ['sn' => 32, 'full_name' => 'Adebayo Amidat', 'class' => 'KG II', 'section' => 'A', 'email' => 'a.amidat@abuja.bestsolution.ng'],
    ['sn' => 33, 'full_name' => 'Adeyemo Sarah', 'class' => 'KG II', 'section' => 'A', 'email' => 'a.sarah@abuja.bestsolution.ng'],
    ['sn' => 34, 'full_name' => 'Aladegbami Isaac', 'class' => 'KG II', 'section' => 'A', 'email' => 'a.isaac@abuja.bestsolution.ng'],
    ['sn' => 35, 'full_name' => 'Dela David', 'class' => 'KG II', 'section' => 'A', 'email' => 'd.david@abuja.bestsolution.ng'],
    ['sn' => 36, 'full_name' => 'Garba Mubasharoh', 'class' => 'KG II', 'section' => 'A', 'email' => 'g.mubasharoh@abuja.bestsolution.ng'],
    ['sn' => 37, 'full_name' => 'Iliyasu Marvelous', 'class' => 'KG II', 'section' => 'A', 'email' => 'i.marvelous@abuja.bestsolution.ng'],
    ['sn' => 38, 'full_name' => 'Liveth Deborah', 'class' => 'KG II', 'section' => 'A', 'email' => 'l.deborah@abuja.bestsolution.ng'],
    ['sn' => 39, 'full_name' => 'Micheal Mathias', 'class' => 'KG II', 'section' => 'A', 'email' => 'm.mathias@abuja.bestsolution.ng'],
    ['sn' => 40, 'full_name' => 'Muhammed Anas', 'class' => 'KG II', 'section' => 'A', 'email' => 'm.anas@abuja.bestsolution.ng'],
    ['sn' => 41, 'full_name' => 'Mukthar Shakir', 'class' => 'KG II', 'section' => 'A', 'email' => 'm.shakir@abuja.bestsolution.ng'],
    ['sn' => 42, 'full_name' => 'Nuhu Haruna Hauwa', 'class' => 'KG II', 'section' => 'A', 'email' => 'n.hauwa@abuja.bestsolution.ng'],
    ['sn' => 43, 'full_name' => 'Obaje Esther Jasmine', 'class' => 'KG II', 'section' => 'A', 'email' => 'o.jasmine@abuja.bestsolution.ng'],
    ['sn' => 44, 'full_name' => 'Oluwadare Fortune', 'class' => 'KG II', 'section' => 'A', 'email' => 'o.fortune@abuja.bestsolution.ng'],
    ['sn' => 45, 'full_name' => 'Omotosho David', 'class' => 'KG II', 'section' => 'A', 'email' => 'o.david@abuja.bestsolution.ng'],
    ['sn' => 46, 'full_name' => 'Oyeleye Oluwashidara', 'class' => 'KG II', 'section' => 'A', 'email' => 'o.oluwashidara@abuja.bestsolution.ng'],
    ['sn' => 47, 'full_name' => 'Peter Perszy', 'class' => 'KG II', 'section' => 'A', 'email' => 'p.perszy@abuja.bestsolution.ng'],
    ['sn' => 48, 'full_name' => 'Rapheal Timothy', 'class' => 'KG II', 'section' => 'A', 'email' => 'r.timothy@abuja.bestsolution.ng'],
    ['sn' => 49, 'full_name' => 'Saliu Hummul Salma', 'class' => 'KG II', 'section' => 'A', 'email' => 's.salma@abuja.bestsolution.ng'],
    ['sn' => 50, 'full_name' => 'Yunusa Fatimah', 'class' => 'KG II', 'section' => 'A', 'email' => 'y.fatimah@abuja.bestsolution.ng'],
    ['sn' => 51, 'full_name' => 'Adebayo Jamaldeen', 'class' => 'Nursery 1', 'section' => 'A', 'email' => 'a.jamaldeen@abuja.bestsolution.ng'],
    ['sn' => 52, 'full_name' => 'Adeniyi Sumaiya', 'class' => 'Nursery 1', 'section' => 'A', 'email' => 'a.sumaiya@abuja.bestsolution.ng'],
    ['sn' => 53, 'full_name' => 'Adetokunbo Idowu', 'class' => 'Nursery 1', 'section' => 'A', 'email' => 'a.idowu@abuja.bestsolution.ng'],
    ['sn' => 54, 'full_name' => 'Adeyemo Fathia', 'class' => 'Nursery 1', 'section' => 'A', 'email' => 'a.fathia@abuja.bestsolution.ng'],
    ['sn' => 55, 'full_name' => 'Ameh Makarios', 'class' => 'Nursery 1', 'section' => 'A', 'email' => 'a.makarios@abuja.bestsolution.ng'],
    ['sn' => 56, 'full_name' => 'Bello Nimat', 'class' => 'Nursery 1', 'section' => 'A', 'email' => 'b.nimat@abuja.bestsolution.ng'],
    ['sn' => 57, 'full_name' => 'Francis Collins', 'class' => 'Nursery 1', 'section' => 'A', 'email' => 'f.collins@abuja.bestsolution.ng'],
    ['sn' => 58, 'full_name' => 'Garba Iman', 'class' => 'Nursery 1', 'section' => 'A', 'email' => 'g.iman@abuja.bestsolution.ng'],
    ['sn' => 59, 'full_name' => 'Idongesit Imabong', 'class' => 'Nursery 1', 'section' => 'A', 'email' => 'i.imabong@abuja.bestsolution.ng'],
    ['sn' => 60, 'full_name' => 'Isiaka Toheebat', 'class' => 'Nursery 1', 'section' => 'A', 'email' => 'i.toheebat@abuja.bestsolution.ng'],
    ['sn' => 61, 'full_name' => 'Livinus Abigail', 'class' => 'Nursery 1', 'section' => 'A', 'email' => 'l.abigail@abuja.bestsolution.ng'],
    ['sn' => 62, 'full_name' => 'Micheal Bright', 'class' => 'Nursery 1', 'section' => 'A', 'email' => 'm.bright@abuja.bestsolution.ng'],
    ['sn' => 63, 'full_name' => 'Muhammed Ameenat', 'class' => 'Nursery 1', 'section' => 'A', 'email' => 'm.ameenat@abuja.bestsolution.ng'],
    ['sn' => 64, 'full_name' => 'Muhammed Ameerat', 'class' => 'Nursery 1', 'section' => 'A', 'email' => 'm.ameerat@abuja.bestsolution.ng'],
    ['sn' => 65, 'full_name' => 'Malangai Enoch', 'class' => 'Nursery 1', 'section' => 'A', 'email' => 'm.enoch@abuja.bestsolution.ng'],
    ['sn' => 66, 'full_name' => 'Noah Sophia', 'class' => 'Nursery 1', 'section' => 'A', 'email' => 'n.sophia@abuja.bestsolution.ng'],
    ['sn' => 67, 'full_name' => 'Nuhu .M. Amir', 'class' => 'Nursery 1', 'section' => 'A', 'email' => 'n.amir@abuja.bestsolution.ng'],
    ['sn' => 68, 'full_name' => 'Nwaeze Akachukwu', 'class' => 'Nursery 1', 'section' => 'A', 'email' => 'n.akachukwu@abuja.bestsolution.ng'],
    ['sn' => 69, 'full_name' => 'Onuegbu Bryan', 'class' => 'Nursery 1', 'section' => 'A', 'email' => 'o.bryan@abuja.bestsolution.ng'],
    ['sn' => 70, 'full_name' => 'Odejobi Habibah', 'class' => 'Nursery 1', 'section' => 'A', 'email' => 'o.habibah@abuja.bestsolution.ng'],
    ['sn' => 71, 'full_name' => 'Samuel Nathan', 'class' => 'Nursery 1', 'section' => 'A', 'email' => 's.nathan@abuja.bestsolution.ng'],
    ['sn' => 72, 'full_name' => 'John Treasure', 'class' => 'Nursery 1', 'section' => 'A', 'email' => 'j.treasure@abuja.bestsolution.ng'],
    ['sn' => 73, 'full_name' => 'Wenon Anas', 'class' => 'Nursery 1', 'section' => 'A', 'email' => 'w.anas@abuja.bestsolution.ng'],
    ['sn' => 74, 'full_name' => 'Winifred Chukwudi', 'class' => 'Nursery 1', 'section' => 'A', 'email' => 'w.chukwudi@abuja.bestsolution.ng'],
    ['sn' => 75, 'full_name' => 'Yusuf Khairat', 'class' => 'Nursery 1', 'section' => 'A', 'email' => 'y.khairat@abuja.bestsolution.ng'],
    ['sn' => 76, 'full_name' => 'Adeniyi Dorcas', 'class' => 'Primary 1', 'section' => 'A', 'email' => 'a.dorcas@abuja.bestsolution.ng'],
    ['sn' => 77, 'full_name' => 'Amodu Mohammed', 'class' => 'Primary 1', 'section' => 'A', 'email' => 'a.mohammed@abuja.bestsolution.ng'],
    ['sn' => 78, 'full_name' => 'Ayuba Victory', 'class' => 'Primary 1', 'section' => 'A', 'email' => 'a.victory2@abuja.bestsolution.ng'],
    ['sn' => 79, 'full_name' => 'Abdulkareem Abdulmalik', 'class' => 'Primary 1', 'section' => 'A', 'email' => 'a.abdulmalik@abuja.bestsolution.ng'],
    ['sn' => 80, 'full_name' => 'Ameh Matilda', 'class' => 'Primary 1', 'section' => 'A', 'email' => 'a.matilda@abuja.bestsolution.ng'],
    ['sn' => 81, 'full_name' => 'Edwin Joy', 'class' => 'Primary 1', 'section' => 'A', 'email' => 'e.joy@abuja.bestsolution.ng'],
    ['sn' => 82, 'full_name' => 'Houtessamion Glory', 'class' => 'Primary 1', 'section' => 'A', 'email' => 'h.glory@abuja.bestsolution.ng'],
    ['sn' => 83, 'full_name' => 'Haruna Abubakar', 'class' => 'Primary 1', 'section' => 'A', 'email' => 'h.abubakar@abuja.bestsolution.ng'],
    ['sn' => 84, 'full_name' => 'Livinus Ivan', 'class' => 'Primary 1', 'section' => 'A', 'email' => 'l.ivan@abuja.bestsolution.ng'],
    ['sn' => 85, 'full_name' => 'Malanghi Mary', 'class' => 'Primary 1', 'section' => 'A', 'email' => 'm.mary@abuja.bestsolution.ng'],
    ['sn' => 86, 'full_name' => 'Mohammed Faiza', 'class' => 'Primary 1', 'section' => 'A', 'email' => 'm.faiza@abuja.bestsolution.ng'],
    ['sn' => 87, 'full_name' => 'Obaniyi Dinah', 'class' => 'Primary 1', 'section' => 'A', 'email' => 'o.dinah@abuja.bestsolution.ng'],
    ['sn' => 88, 'full_name' => 'Oyewole Zoe', 'class' => 'Primary 1', 'section' => 'A', 'email' => 'o.zoe@abuja.bestsolution.ng'],
    ['sn' => 89, 'full_name' => 'Peter Praise', 'class' => 'Primary 1', 'section' => 'A', 'email' => 'p.praise@abuja.bestsolution.ng'],
    ['sn' => 90, 'full_name' => 'Samuel Shedrack', 'class' => 'Primary 1', 'section' => 'A', 'email' => 's.shedrack@abuja.bestsolution.ng'],
    ['sn' => 91, 'full_name' => 'Samson Joshua', 'class' => 'Primary 1', 'section' => 'A', 'email' => 's.joshua@abuja.bestsolution.ng'],
    ['sn' => 92, 'full_name' => 'Stephen Angel', 'class' => 'Primary 1', 'section' => 'A', 'email' => 's.angel@abuja.bestsolution.ng'],
    ['sn' => 93, 'full_name' => 'William Kwoopnan', 'class' => 'Primary 1', 'section' => 'A', 'email' => 'w.kwoopnan@abuja.bestsolution.ng'],
    ['sn' => 94, 'full_name' => 'Yunusa Billah', 'class' => 'Primary 1', 'section' => 'A', 'email' => 'y.billah@abuja.bestsolution.ng'],
    ['sn' => 95, 'full_name' => 'Abdulganiyu Mutialib', 'class' => 'Primary 2', 'section' => 'A', 'email' => 'a.mutialib@abuja.bestsolution.ng'],
    ['sn' => 96, 'full_name' => 'Adediran Fatimah', 'class' => 'Primary 2', 'section' => 'A', 'email' => 'a.fatimah@abuja.bestsolution.ng'],
    ['sn' => 97, 'full_name' => 'Christopher Deborah', 'class' => 'Primary 2', 'section' => 'A', 'email' => 'c.deborah@abuja.bestsolution.ng'],
    ['sn' => 98, 'full_name' => 'Ehiwele Favour', 'class' => 'Primary 2', 'section' => 'A', 'email' => 'e.favour@abuja.bestsolution.ng'],
    ['sn' => 99, 'full_name' => 'Emmanuel Emmanuella', 'class' => 'Primary 2', 'section' => 'A', 'email' => 'e.emmanuella@abuja.bestsolution.ng'],
    ['sn' => 100, 'full_name' => 'Idongesit Precious I.', 'class' => 'Primary 2', 'section' => 'A', 'email' => 'i.precious@abuja.bestsolution.ng'],
    ['sn' => 101, 'full_name' => 'Ikechukwu Kosi F.', 'class' => 'Primary 2', 'section' => 'A', 'email' => 'i.kosi@abuja.bestsolution.ng'],
    ['sn' => 102, 'full_name' => 'Ikpea O. Yvonne', 'class' => 'Primary 2', 'section' => 'A', 'email' => 'i.yvonne@abuja.bestsolution.ng'],
    ['sn' => 103, 'full_name' => 'James Chika Goodness', 'class' => 'Primary 2', 'section' => 'A', 'email' => 'j.goodness@abuja.bestsolution.ng'],
    ['sn' => 104, 'full_name' => 'Nwachukwu Daniel', 'class' => 'Primary 2', 'section' => 'A', 'email' => 'n.daniel@abuja.bestsolution.ng'],
    ['sn' => 105, 'full_name' => 'Odekale Iyole', 'class' => 'Primary 2', 'section' => 'A', 'email' => 'o.iyole@abuja.bestsolution.ng'],
    ['sn' => 106, 'full_name' => 'Ogunlade Mohammed', 'class' => 'Primary 2', 'section' => 'A', 'email' => 'o.mohammed@abuja.bestsolution.ng'],
    ['sn' => 107, 'full_name' => 'Omashi Praise', 'class' => 'Primary 2', 'section' => 'A', 'email' => 'o.praise@abuja.bestsolution.ng'],
    ['sn' => 108, 'full_name' => 'Omashi Purity', 'class' => 'Primary 2', 'section' => 'A', 'email' => 'o.purity@abuja.bestsolution.ng'],
    ['sn' => 109, 'full_name' => 'Olorunmaye Peace', 'class' => 'Primary 2', 'section' => 'A', 'email' => 'o.peace@abuja.bestsolution.ng'],
    ['sn' => 110, 'full_name' => 'Samuel Ethan', 'class' => 'Primary 2', 'section' => 'A', 'email' => 's.ethan@abuja.bestsolution.ng'],
    ['sn' => 111, 'full_name' => 'Francis Judith', 'class' => 'Primary 2', 'section' => 'A', 'email' => 'f.judith@abuja.bestsolution.ng'],
    ['sn' => 112, 'full_name' => 'Abubakar Ali', 'class' => 'Primary 2', 'section' => 'A', 'email' => 'a.ali@abuja.bestsolution.ng'],
    ['sn' => 113, 'full_name' => 'Ayuba Aaron', 'class' => 'Primary 3', 'section' => 'A', 'email' => 'a.aaron@abuja.bestsolution.ng'],
    ['sn' => 114, 'full_name' => 'Adeyemi E. Joshua', 'class' => 'Primary 3', 'section' => 'A', 'email' => 'a.joshua@abuja.bestsolution.ng'],
    ['sn' => 115, 'full_name' => 'Ajeh E. Peace', 'class' => 'Primary 3', 'section' => 'A', 'email' => 'a.peace@abuja.bestsolution.ng'],
    ['sn' => 116, 'full_name' => 'Ataba S. Habib', 'class' => 'Primary 3', 'section' => 'A', 'email' => 'a.habib@abuja.bestsolution.ng'],
    ['sn' => 117, 'full_name' => 'Bajulaye Ifeoluwa', 'class' => 'Primary 3', 'section' => 'A', 'email' => 'b.ifeoluwa@abuja.bestsolution.ng'],
    ['sn' => 118, 'full_name' => 'Bitrus Faith', 'class' => 'Primary 3', 'section' => 'A', 'email' => 'b.faith@abuja.bestsolution.ng'],
    ['sn' => 119, 'full_name' => 'Emmanuel Victory', 'class' => 'Primary 3', 'section' => 'A', 'email' => 'e.victory@abuja.bestsolution.ng'],
    ['sn' => 120, 'full_name' => 'Eruogi Precious', 'class' => 'Primary 3', 'section' => 'A', 'email' => 'e.precious2@abuja.bestsolution.ng'],
    ['sn' => 121, 'full_name' => 'Ibrahim Musinat', 'class' => 'Primary 3', 'section' => 'A', 'email' => 'i.musinat@abuja.bestsolution.ng'],
    ['sn' => 122, 'full_name' => 'Iliyasu Emmanuella', 'class' => 'Primary 3', 'section' => 'A', 'email' => 'i.emmanuella@abuja.bestsolution.ng'],
    ['sn' => 123, 'full_name' => 'Martins C. Erica', 'class' => 'Primary 3', 'section' => 'A', 'email' => 'm.erica@abuja.bestsolution.ng'],
    ['sn' => 124, 'full_name' => 'Mba Akachi', 'class' => 'Primary 3', 'section' => 'A', 'email' => 'm.akachi@abuja.bestsolution.ng'],
    ['sn' => 125, 'full_name' => 'Mohammed Akram', 'class' => 'Primary 3', 'section' => 'A', 'email' => 'm.akram@abuja.bestsolution.ng'],
    ['sn' => 126, 'full_name' => 'Muhammed Kamilu', 'class' => 'Primary 3', 'section' => 'A', 'email' => 'm.kamilu@abuja.bestsolution.ng'],
    ['sn' => 127, 'full_name' => 'Noah E. Shalom', 'class' => 'Primary 3', 'section' => 'A', 'email' => 'n.shalom@abuja.bestsolution.ng'],
    ['sn' => 128, 'full_name' => 'Obaniyi F. Daniel', 'class' => 'Primary 3', 'section' => 'A', 'email' => 'o.daniel2@abuja.bestsolution.ng'],
    ['sn' => 129, 'full_name' => 'Olamide Seyi L.', 'class' => 'Primary 3', 'section' => 'A', 'email' => 'o.seyi@abuja.bestsolution.ng'],
    ['sn' => 130, 'full_name' => 'Olododo Samuel', 'class' => 'Primary 3', 'section' => 'A', 'email' => 'o.samuel@abuja.bestsolution.ng'],
    ['sn' => 131, 'full_name' => 'Temitayo Destiny', 'class' => 'Primary 3', 'section' => 'A', 'email' => 't.destiny@abuja.bestsolution.ng'],
    ['sn' => 132, 'full_name' => 'Umeanyo C. Christabel', 'class' => 'Primary 3', 'section' => 'A', 'email' => 'u.christabel@abuja.bestsolution.ng'],
    ['sn' => 133, 'full_name' => 'Yusuf Hadinan', 'class' => 'Primary 3', 'section' => 'A', 'email' => 'y.hadinan@abuja.bestsolution.ng'],
    ['sn' => 134, 'full_name' => 'Nnawugo Munachim', 'class' => 'Primary 3', 'section' => 'A', 'email' => 'n.munachim@abuja.bestsolution.ng'],
    ['sn' => 135, 'full_name' => 'Adebayo Halima', 'class' => 'Primary 4', 'section' => 'A', 'email' => 'a.halima@abuja.bestsolution.ng'],
    ['sn' => 136, 'full_name' => 'Adetokunbo Taiye', 'class' => 'Primary 4', 'section' => 'A', 'email' => 'a.taiye@abuja.bestsolution.ng'],
    ['sn' => 137, 'full_name' => 'Arogunjo Sunday', 'class' => 'Primary 4', 'section' => 'A', 'email' => 'a.sunday@abuja.bestsolution.ng'],
    ['sn' => 138, 'full_name' => 'Daramola Inioluwa', 'class' => 'Primary 4', 'section' => 'A', 'email' => 'd.inioluwa@abuja.bestsolution.ng'],
    ['sn' => 139, 'full_name' => 'Ezekiel Precious', 'class' => 'Primary 4', 'section' => 'A', 'email' => 'e.precious3@abuja.bestsolution.ng'],
    ['sn' => 140, 'full_name' => 'Elom Elijah', 'class' => 'Primary 4', 'section' => 'A', 'email' => 'e.elijah@abuja.bestsolution.ng'],
    ['sn' => 141, 'full_name' => 'Huntessomo Hossana', 'class' => 'Primary 4', 'section' => 'A', 'email' => 'h.hossana@abuja.bestsolution.ng'],
    ['sn' => 142, 'full_name' => 'Ikechukwu Divine', 'class' => 'Primary 4', 'section' => 'A', 'email' => 'i.divine@abuja.bestsolution.ng'],
    ['sn' => 143, 'full_name' => 'James Gift', 'class' => 'Primary 4', 'section' => 'A', 'email' => 'j.gift@abuja.bestsolution.ng'],
    ['sn' => 144, 'full_name' => 'Muhammed Umu-Salmat', 'class' => 'Primary 4', 'section' => 'A', 'email' => 'm.salmat@abuja.bestsolution.ng'],
    ['sn' => 145, 'full_name' => 'Olaniyi Hikmat', 'class' => 'Primary 4', 'section' => 'A', 'email' => 'o.hikmat@abuja.bestsolution.ng'],
    ['sn' => 146, 'full_name' => 'Oluwadare Esther', 'class' => 'Primary 4', 'section' => 'A', 'email' => 'o.esther@abuja.bestsolution.ng'],
    ['sn' => 147, 'full_name' => 'Reuben K. Livinus', 'class' => 'Primary 4', 'section' => 'A', 'email' => 'r.livinus@abuja.bestsolution.ng'],
    ['sn' => 148, 'full_name' => 'Abdulrahaman Mufidat', 'class' => 'Primary 4', 'section' => 'B', 'email' => 'a.mufidat@abuja.bestsolution.ng'],
    ['sn' => 149, 'full_name' => 'Amodu Zainab', 'class' => 'Primary 4', 'section' => 'B', 'email' => 'a.zainab@abuja.bestsolution.ng'],
    ['sn' => 150, 'full_name' => 'Benard Patience', 'class' => 'Primary 4', 'section' => 'B', 'email' => 'b.patience@abuja.bestsolution.ng'],
    ['sn' => 151, 'full_name' => 'Bitrus Favour', 'class' => 'Primary 4', 'section' => 'B', 'email' => 'b.favour@abuja.bestsolution.ng'],
    ['sn' => 152, 'full_name' => 'Chioke Rejoice', 'class' => 'Primary 4', 'section' => 'B', 'email' => 'c.rejoice@abuja.bestsolution.ng'],
    ['sn' => 153, 'full_name' => 'Christopher Victor', 'class' => 'Primary 4', 'section' => 'B', 'email' => 'c.victor@abuja.bestsolution.ng'],
    ['sn' => 154, 'full_name' => 'Francis Isaiah', 'class' => 'Primary 4', 'section' => 'B', 'email' => 'f.isaiah@abuja.bestsolution.ng'],
    ['sn' => 155, 'full_name' => 'Ibrahim Lateefat', 'class' => 'Primary 4', 'section' => 'B', 'email' => 'i.lateefat@abuja.bestsolution.ng'],
    ['sn' => 156, 'full_name' => 'Idongesit Praise', 'class' => 'Primary 4', 'section' => 'B', 'email' => 'i.praise@abuja.bestsolution.ng'],
    ['sn' => 157, 'full_name' => 'Nathaniel Joan', 'class' => 'Primary 4', 'section' => 'B', 'email' => 'n.joan@abuja.bestsolution.ng'],
    ['sn' => 158, 'full_name' => 'Nuhu Prosper', 'class' => 'Primary 4', 'section' => 'B', 'email' => 'n.prosper@abuja.bestsolution.ng'],
    ['sn' => 159, 'full_name' => 'Odekale Mayowa', 'class' => 'Primary 4', 'section' => 'B', 'email' => 'o.mayowa@abuja.bestsolution.ng'],
    ['sn' => 160, 'full_name' => 'Olarewaju Olamide', 'class' => 'Primary 4', 'section' => 'B', 'email' => 'o.olamide@abuja.bestsolution.ng'],
    ['sn' => 161, 'full_name' => 'Olorunmaye Mark', 'class' => 'Primary 4', 'section' => 'B', 'email' => 'o.mark@abuja.bestsolution.ng'],
    ['sn' => 162, 'full_name' => 'Oyewole Elzira', 'class' => 'Primary 4', 'section' => 'B', 'email' => 'o.elzira@abuja.bestsolution.ng'],
    ['sn' => 163, 'full_name' => 'Peter Prudence', 'class' => 'Primary 4', 'section' => 'B', 'email' => 'p.prudence@abuja.bestsolution.ng'],
    ['sn' => 164, 'full_name' => 'Samson Nnaji', 'class' => 'Primary 4', 'section' => 'B', 'email' => 's.nnaji@abuja.bestsolution.ng'],
    ['sn' => 165, 'full_name' => 'Samuel Bernice', 'class' => 'Primary 4', 'section' => 'B', 'email' => 's.bernice@abuja.bestsolution.ng'],
    ['sn' => 166, 'full_name' => 'Adetokunbo Kehinde', 'class' => 'Primary 4', 'section' => 'B', 'email' => 'a.kehinde@abuja.bestsolution.ng'],
    ['sn' => 167, 'full_name' => 'Onoebu Chimobi', 'class' => 'Primary 4', 'section' => 'B', 'email' => 'o.chimobi@abuja.bestsolution.ng'],
    ['sn' => 168, 'full_name' => 'Abua Diana', 'class' => 'Primary 5', 'section' => 'A', 'email' => 'a.diana@abuja.bestsolution.ng'],
    ['sn' => 169, 'full_name' => 'Adediran Aminat', 'class' => 'Primary 5', 'section' => 'A', 'email' => 'a.aminat@abuja.bestsolution.ng'],
    ['sn' => 170, 'full_name' => 'Adeniyi Mary', 'class' => 'Primary 5', 'section' => 'A', 'email' => 'a.mary@abuja.bestsolution.ng'],
    ['sn' => 171, 'full_name' => 'Anselm Miracle', 'class' => 'Primary 5', 'section' => 'A', 'email' => 'a.miracle@abuja.bestsolution.ng'],
    ['sn' => 172, 'full_name' => 'Chizoba Excel', 'class' => 'Primary 5', 'section' => 'A', 'email' => 'c.excel@abuja.bestsolution.ng'],
    ['sn' => 173, 'full_name' => 'Christopher David', 'class' => 'Primary 5', 'section' => 'A', 'email' => 'c.david@abuja.bestsolution.ng'],
    ['sn' => 174, 'full_name' => 'Emmanuel Success', 'class' => 'Primary 5', 'section' => 'A', 'email' => 'e.success@abuja.bestsolution.ng'],
    ['sn' => 175, 'full_name' => 'Erinfolami Ogooluwa', 'class' => 'Primary 5', 'section' => 'A', 'email' => 'e.ogooluwa@abuja.bestsolution.ng'],
    ['sn' => 176, 'full_name' => 'Jolayemi Enoch', 'class' => 'Primary 5', 'section' => 'A', 'email' => 'j.enoch@abuja.bestsolution.ng'],
    ['sn' => 177, 'full_name' => 'Jubril Favour', 'class' => 'Primary 5', 'section' => 'A', 'email' => 'j.favour@abuja.bestsolution.ng'],
    ['sn' => 178, 'full_name' => 'John Isaac', 'class' => 'Primary 5', 'section' => 'A', 'email' => 'j.isaac@abuja.bestsolution.ng'],
    ['sn' => 179, 'full_name' => 'Mathew Winifred', 'class' => 'Primary 5', 'section' => 'A', 'email' => 'm.winifred@abuja.bestsolution.ng'],
    ['sn' => 180, 'full_name' => 'Muhammed Ashraff', 'class' => 'Primary 5', 'section' => 'A', 'email' => 'm.ashraff@abuja.bestsolution.ng'],
    ['sn' => 181, 'full_name' => 'Raphael Rhoda', 'class' => 'Primary 5', 'section' => 'A', 'email' => 'r.rhoda@abuja.bestsolution.ng'],
    ['sn' => 182, 'full_name' => 'Warik Habila', 'class' => 'Primary 5', 'section' => 'A', 'email' => 'w.habila@abuja.bestsolution.ng'],
    ['sn' => 183, 'full_name' => 'Abdulkareem Aishat', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'a.aishat@abuja.bestsolution.ng'],
    ['sn' => 184, 'full_name' => 'Abdulrahman Abdulkarim', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'a.abdulkarim@abuja.bestsolution.ng'],
    ['sn' => 185, 'full_name' => 'Abdulwasiu Maridiyat', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'a.maridiyat@abuja.bestsolution.ng'],
    ['sn' => 186, 'full_name' => 'Adeyemo Awwal', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'a.awwal@abuja.bestsolution.ng'],
    ['sn' => 187, 'full_name' => 'Akinwale Abdulganiyu', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'a.abdulganiyu@abuja.bestsolution.ng'],
    ['sn' => 188, 'full_name' => 'Bitrus Sunday', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'b.sunday@abuja.bestsolution.ng'],
    ['sn' => 189, 'full_name' => 'Bolarinwa Tolani', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'b.tolani@abuja.bestsolution.ng'],
    ['sn' => 190, 'full_name' => 'Danazumi Tallyson', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'd.tallyson@abuja.bestsolution.ng'],
    ['sn' => 191, 'full_name' => 'Danjuma Patience', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'd.patience@abuja.bestsolution.ng'],
    ['sn' => 192, 'full_name' => 'Dela Deborah', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'd.deborah@abuja.bestsolution.ng'],
    ['sn' => 193, 'full_name' => 'Francis Emmanuel', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'f.emmanuel@abuja.bestsolution.ng'],
    ['sn' => 194, 'full_name' => 'Haruna Zeenat', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'h.zeenat@abuja.bestsolution.ng'],
    ['sn' => 195, 'full_name' => 'Malangai Favour', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'm.favour@abuja.bestsolution.ng'],
    ['sn' => 196, 'full_name' => 'Michael Mishael', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'm.mishael@abuja.bestsolution.ng'],
    ['sn' => 197, 'full_name' => 'Muftau Ibrahim', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'm.ibrahim@abuja.bestsolution.ng'],
    ['sn' => 198, 'full_name' => 'Muhammed Anas', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'm.anas2@abuja.bestsolution.ng'],
    ['sn' => 199, 'full_name' => 'Nathaniel Oyinbobola', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'n.oyinbobola@abuja.bestsolution.ng'],
    ['sn' => 200, 'full_name' => 'Nwachukwu Somto', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'n.somto@abuja.bestsolution.ng'],
    ['sn' => 201, 'full_name' => 'Nwaeze Uju', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'n.uju@abuja.bestsolution.ng'],
    ['sn' => 202, 'full_name' => 'Ogbonna Chidozie', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'o.chidozie@abuja.bestsolution.ng'],
    ['sn' => 203, 'full_name' => 'Okolie Emmanuella', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'o.emmanuella@abuja.bestsolution.ng'],
    ['sn' => 204, 'full_name' => 'Olusanya Jemaima', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'o.jemaima@abuja.bestsolution.ng'],
    ['sn' => 205, 'full_name' => 'Owoeye David', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'o.david2@abuja.bestsolution.ng'],
    ['sn' => 206, 'full_name' => 'Oyeleye Nifemi', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'o.nifemi@abuja.bestsolution.ng'],
    ['sn' => 207, 'full_name' => 'Isaac Chiroma', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'i.chiroma@abuja.bestsolution.ng'],
    ['sn' => 208, 'full_name' => 'Patsimon Joshua', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'p.joshua@abuja.bestsolution.ng'],
    ['sn' => 209, 'full_name' => 'Stephen Favour', 'class' => 'JSS 1', 'section' => 'A', 'email' => 's.favour@abuja.bestsolution.ng'],
    ['sn' => 210, 'full_name' => 'Stephen Wisdom', 'class' => 'JSS 1', 'section' => 'A', 'email' => 's.wisdom@abuja.bestsolution.ng'],
    ['sn' => 211, 'full_name' => 'Victor Chika', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'v.chika@abuja.bestsolution.ng'],
    ['sn' => 212, 'full_name' => 'Yusuf Zeyyad', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'y.zeyyad@abuja.bestsolution.ng'],
    ['sn' => 213, 'full_name' => 'Chisimdi Gods Light G.', 'class' => 'JSS 1', 'section' => 'A', 'email' => 'c.light@abuja.bestsolution.ng'],
    ['sn' => 214, 'full_name' => 'Abdul Ganiyu Khaleed', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'a.khaleed@abuja.bestsolution.ng'],
    ['sn' => 215, 'full_name' => 'Abel Doreen', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'a.doreen@abuja.bestsolution.ng'],
    ['sn' => 216, 'full_name' => 'Adediran Okiki', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'a.okiki@abuja.bestsolution.ng'],
    ['sn' => 217, 'full_name' => 'Afolabi Favour', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'a.favour2@abuja.bestsolution.ng'],
    ['sn' => 218, 'full_name' => 'Amelinya Ifeoluwa', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'a.ifeoluwa@abuja.bestsolution.ng'],
    ['sn' => 219, 'full_name' => 'Daniel O. Dorcas', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'd.dorcas@abuja.bestsolution.ng'],
    ['sn' => 220, 'full_name' => 'Emmanuel Precious', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'e.precious4@abuja.bestsolution.ng'],
    ['sn' => 221, 'full_name' => 'Jubril Lucky', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'j.lucky@abuja.bestsolution.ng'],
    ['sn' => 222, 'full_name' => 'James Stephanie', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'j.stephanie@abuja.bestsolution.ng'],
    ['sn' => 223, 'full_name' => 'Lucky Bright', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'l.bright@abuja.bestsolution.ng'],
    ['sn' => 224, 'full_name' => 'John Wisdom', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'j.wisdom@abuja.bestsolution.ng'],
    ['sn' => 225, 'full_name' => 'Matthews Daniel', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'm.daniel@abuja.bestsolution.ng'],
    ['sn' => 226, 'full_name' => 'Mohammed Yesira', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'm.yesira@abuja.bestsolution.ng'],
    ['sn' => 227, 'full_name' => 'Musa Fausal Yetu', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'm.yetu@abuja.bestsolution.ng'],
    ['sn' => 228, 'full_name' => 'Mammong Emmanuel', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'm.emmanuel@abuja.bestsolution.ng'],
    ['sn' => 229, 'full_name' => 'Nnawugo Kosi', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'n.kosi@abuja.bestsolution.ng'],
    ['sn' => 230, 'full_name' => 'Nurudeen Khalifa', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'n.khalifa@abuja.bestsolution.ng'],
    ['sn' => 231, 'full_name' => 'Obaniyi Fukayomi', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'o.fukayomi@abuja.bestsolution.ng'],
    ['sn' => 232, 'full_name' => 'Olorumaye Hephzibah', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'o.hephzibah@abuja.bestsolution.ng'],
    ['sn' => 233, 'full_name' => 'Okiki Roland Zoin', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'o.zoin@abuja.bestsolution.ng'],
    ['sn' => 234, 'full_name' => 'Olayoye Azeezat', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'o.azeezat@abuja.bestsolution.ng'],
    ['sn' => 235, 'full_name' => 'Oluwadare Daniel', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'o.daniel3@abuja.bestsolution.ng'],
    ['sn' => 236, 'full_name' => 'Osejemie Firewamire', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'o.firewamire@abuja.bestsolution.ng'],
    ['sn' => 237, 'full_name' => 'Odekale Kabeebat', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'o.kabeebat@abuja.bestsolution.ng'],
    ['sn' => 238, 'full_name' => 'Raphel Mary', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'r.mary@abuja.bestsolution.ng'],
    ['sn' => 239, 'full_name' => 'Nwachukwu Victor', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'n.victor@abuja.bestsolution.ng'],
    ['sn' => 240, 'full_name' => 'Sadiq Ninda S.', 'class' => 'JSS 2', 'section' => 'A', 'email' => 's.ninda@abuja.bestsolution.ng'],
    ['sn' => 241, 'full_name' => 'Musa Medinat', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'm.medinat@abuja.bestsolution.ng'],
    ['sn' => 242, 'full_name' => 'Chinedu Emmanuel', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'c.emmanuel@abuja.bestsolution.ng'],
    ['sn' => 243, 'full_name' => 'Adetokunbo Teniola', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'a.teniola@abuja.bestsolution.ng'],
    ['sn' => 244, 'full_name' => 'Emmanuel O. Godsgift', 'class' => 'JSS 2', 'section' => 'A', 'email' => 'e.godsgift@abuja.bestsolution.ng'],
    ['sn' => 245, 'full_name' => 'Abua Sophia', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'a.sophia@abuja.bestsolution.ng'],
    ['sn' => 246, 'full_name' => 'Adeniyi Ramadan', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'a.ramadan@abuja.bestsolution.ng'],
    ['sn' => 247, 'full_name' => 'Adewumi Enoch', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'a.enoch@abuja.bestsolution.ng'],
    ['sn' => 248, 'full_name' => 'Akinola Fortune Ajayi', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'a.ajayi@abuja.bestsolution.ng'],
    ['sn' => 249, 'full_name' => 'AlamaJu O. Excel', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'a.excel@abuja.bestsolution.ng'],
    ['sn' => 250, 'full_name' => 'Emmanuel Dennis', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'e.dennis@abuja.bestsolution.ng'],
    ['sn' => 251, 'full_name' => 'Erinfolami Duninunu', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'e.duninunu@abuja.bestsolution.ng'],
    ['sn' => 252, 'full_name' => 'Huntessomo Lawrencia', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'h.lawrencia@abuja.bestsolution.ng'],
    ['sn' => 253, 'full_name' => 'Idris Usman', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'i.usman@abuja.bestsolution.ng'],
    ['sn' => 254, 'full_name' => 'Joan Fekret', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'j.fekret@abuja.bestsolution.ng'],
    ['sn' => 255, 'full_name' => 'Joseph Shedrach', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'j.shedrach@abuja.bestsolution.ng'],
    ['sn' => 256, 'full_name' => 'John Francisca', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'j.francisca@abuja.bestsolution.ng'],
    ['sn' => 257, 'full_name' => 'Moshood Alsifa', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'm.alsifa@abuja.bestsolution.ng'],
    ['sn' => 258, 'full_name' => 'Moses Jolayemi', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'm.jolayemi@abuja.bestsolution.ng'],
    ['sn' => 259, 'full_name' => 'Naubussi Emmanuel', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'n.emmanuel@abuja.bestsolution.ng'],
    ['sn' => 260, 'full_name' => 'Nwuogo Chiamaka', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'n.chiamaka@abuja.bestsolution.ng'],
    ['sn' => 261, 'full_name' => 'Odenusi Faith', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'o.faith@abuja.bestsolution.ng'],
    ['sn' => 262, 'full_name' => 'Obejobi Abass', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'o.abass@abuja.bestsolution.ng'],
    ['sn' => 263, 'full_name' => 'Olanipekun Boluwatife', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'o.boluwatife@abuja.bestsolution.ng'],
    ['sn' => 264, 'full_name' => 'Onah Emmanuella', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'o.emmanuella2@abuja.bestsolution.ng'],
    ['sn' => 265, 'full_name' => 'Oyemobi Peace', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'o.peace2@abuja.bestsolution.ng'],
    ['sn' => 266, 'full_name' => 'Pat-Simon King David', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'p.david@abuja.bestsolution.ng'],
    ['sn' => 267, 'full_name' => 'Peter Princilia', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'p.princilia@abuja.bestsolution.ng'],
    ['sn' => 268, 'full_name' => 'Shado Micheal', 'class' => 'JSS 3', 'section' => 'A', 'email' => 's.micheal@abuja.bestsolution.ng'],
    ['sn' => 269, 'full_name' => 'Shehu Rabiu', 'class' => 'JSS 3', 'section' => 'A', 'email' => 's.rabiu@abuja.bestsolution.ng'],
    ['sn' => 270, 'full_name' => 'Sunday Great', 'class' => 'JSS 3', 'section' => 'A', 'email' => 's.great@abuja.bestsolution.ng'],
    ['sn' => 271, 'full_name' => 'Yabir Muhammed', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'y.muhammed@abuja.bestsolution.ng'],
    ['sn' => 272, 'full_name' => 'Yusuf Salim', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'y.salim@abuja.bestsolution.ng'],
    ['sn' => 273, 'full_name' => 'Terhemba Blessing', 'class' => 'JSS 3', 'section' => 'A', 'email' => 't.blessing@abuja.bestsolution.ng'],
    ['sn' => 274, 'full_name' => 'Terhemba Favour', 'class' => 'JSS 3', 'section' => 'A', 'email' => 't.favour@abuja.bestsolution.ng'],
    ['sn' => 275, 'full_name' => 'Tatau Joana', 'class' => 'JSS 3', 'section' => 'A', 'email' => 't.joana@abuja.bestsolution.ng'],
    ['sn' => 276, 'full_name' => 'Chinedu Treasure', 'class' => 'JSS 3', 'section' => 'A', 'email' => 'c.treasure@abuja.bestsolution.ng'],
    ['sn' => 277, 'full_name' => 'Abel Alady Lizzy', 'class' => 'SS 1', 'section' => 'A', 'email' => 'a.lizzy@abuja.bestsolution.ng'],
    ['sn' => 278, 'full_name' => 'Adeyemi Jeremiah O.', 'class' => 'SS 1', 'section' => 'A', 'email' => 'a.jeremiah@abuja.bestsolution.ng'],
    ['sn' => 279, 'full_name' => 'Afolabi David', 'class' => 'SS 1', 'section' => 'A', 'email' => 'a.david@abuja.bestsolution.ng'],
    ['sn' => 280, 'full_name' => 'Agbo Shedrack', 'class' => 'SS 1', 'section' => 'A', 'email' => 'a.shedrack@abuja.bestsolution.ng'],
    ['sn' => 281, 'full_name' => 'Barkindo Pamela', 'class' => 'SS 1', 'section' => 'A', 'email' => 'b.pamela@abuja.bestsolution.ng'],
    ['sn' => 282, 'full_name' => 'Chioke Salvation', 'class' => 'SS 1', 'section' => 'A', 'email' => 'c.salvation@abuja.bestsolution.ng'],
    ['sn' => 283, 'full_name' => 'Chizoba Christabel', 'class' => 'SS 1', 'section' => 'A', 'email' => 'c.christabel@abuja.bestsolution.ng'],
    ['sn' => 284, 'full_name' => 'Emmanuel Shalom', 'class' => 'SS 1', 'section' => 'A', 'email' => 'e.shalom@abuja.bestsolution.ng'],
    ['sn' => 285, 'full_name' => 'Elochukwu C. Hope', 'class' => 'SS 1', 'section' => 'A', 'email' => 'e.hope@abuja.bestsolution.ng'],
    ['sn' => 286, 'full_name' => 'Ibitoye Rodiat', 'class' => 'SS 1', 'section' => 'A', 'email' => 'i.rodiat@abuja.bestsolution.ng'],
    ['sn' => 287, 'full_name' => 'Ibrahim Abdulazeez', 'class' => 'SS 1', 'section' => 'A', 'email' => 'i.abdulazeez@abuja.bestsolution.ng'],
    ['sn' => 288, 'full_name' => 'Ibrahim A. Afsat', 'class' => 'SS 1', 'section' => 'A', 'email' => 'i.afsat@abuja.bestsolution.ng'],
    ['sn' => 289, 'full_name' => 'Idris Tadese', 'class' => 'SS 1', 'section' => 'A', 'email' => 'i.tadese@abuja.bestsolution.ng'],
    ['sn' => 290, 'full_name' => 'James Chibuike', 'class' => 'SS 1', 'section' => 'A', 'email' => 'j.chibuike@abuja.bestsolution.ng'],
    ['sn' => 291, 'full_name' => 'Jatau Lois', 'class' => 'SS 1', 'section' => 'A', 'email' => 'j.lois@abuja.bestsolution.ng'],
    ['sn' => 292, 'full_name' => 'Joseph Mikpi Tehillah', 'class' => 'SS 1', 'section' => 'A', 'email' => 'j.tehillah@abuja.bestsolution.ng'],
    ['sn' => 293, 'full_name' => 'Lawrence Toyosi', 'class' => 'SS 1', 'section' => 'A', 'email' => 'l.toyosi@abuja.bestsolution.ng'],
    ['sn' => 294, 'full_name' => 'Nnaji E. Nicodemus', 'class' => 'SS 1', 'section' => 'A', 'email' => 'n.nicodemus@abuja.bestsolution.ng'],
    ['sn' => 295, 'full_name' => 'Nwachukwu Divine', 'class' => 'SS 1', 'section' => 'A', 'email' => 'n.divine@abuja.bestsolution.ng'],
    ['sn' => 296, 'full_name' => 'Obi Prosper', 'class' => 'SS 1', 'section' => 'A', 'email' => 'o.prosper@abuja.bestsolution.ng'],
    ['sn' => 297, 'full_name' => 'Okezie Goodluck', 'class' => 'SS 1', 'section' => 'A', 'email' => 'o.goodluck@abuja.bestsolution.ng'],
    ['sn' => 298, 'full_name' => 'Okoli Raphaella', 'class' => 'SS 1', 'section' => 'A', 'email' => 'o.raphaella@abuja.bestsolution.ng'],
    ['sn' => 299, 'full_name' => 'Olatoye Abdullahi', 'class' => 'SS 1', 'section' => 'A', 'email' => 'o.abdullahi@abuja.bestsolution.ng'],
    ['sn' => 300, 'full_name' => 'Olukosi Joseph', 'class' => 'SS 1', 'section' => 'A', 'email' => 'o.joseph@abuja.bestsolution.ng'],
    ['sn' => 301, 'full_name' => 'Oluwafemi Esther', 'class' => 'SS 1', 'section' => 'A', 'email' => 'o.esther2@abuja.bestsolution.ng'],
    ['sn' => 302, 'full_name' => 'Onyi J. Fortune', 'class' => 'SS 1', 'section' => 'A', 'email' => 'o.fortune2@abuja.bestsolution.ng'],
    ['sn' => 303, 'full_name' => 'Opoka Charity', 'class' => 'SS 1', 'section' => 'A', 'email' => 'o.charity@abuja.bestsolution.ng'],
    ['sn' => 304, 'full_name' => 'Oloyo A. Emmanuel', 'class' => 'SS 1', 'section' => 'A', 'email' => 'o.emmanuel@abuja.bestsolution.ng'],
    ['sn' => 305, 'full_name' => 'Orinya Joseph', 'class' => 'SS 1', 'section' => 'A', 'email' => 'o.joseph2@abuja.bestsolution.ng'],
    ['sn' => 306, 'full_name' => 'Oyemaobi Ndubisi', 'class' => 'SS 1', 'section' => 'A', 'email' => 'o.ndubisi@abuja.bestsolution.ng'],
    ['sn' => 307, 'full_name' => 'Oyeyipo Blessing', 'class' => 'SS 1', 'section' => 'A', 'email' => 'o.blessing@abuja.bestsolution.ng'],
    ['sn' => 308, 'full_name' => 'Ukpang Goodluck P.', 'class' => 'SS 1', 'section' => 'A', 'email' => 'u.goodluck@abuja.bestsolution.ng'],
    ['sn' => 309, 'full_name' => 'Abdulkareem Kareemat', 'class' => 'SS 1', 'section' => 'B', 'email' => 'a.kareemat@abuja.bestsolution.ng'],
    ['sn' => 310, 'full_name' => 'Adediran A. Faridat', 'class' => 'SS 1', 'section' => 'B', 'email' => 'a.faridat@abuja.bestsolution.ng'],
    ['sn' => 311, 'full_name' => 'Ahmed Maryam', 'class' => 'SS 1', 'section' => 'B', 'email' => 'a.maryam@abuja.bestsolution.ng'],
    ['sn' => 312, 'full_name' => 'Ango John Micheal', 'class' => 'SS 1', 'section' => 'B', 'email' => 'a.micheal@abuja.bestsolution.ng'],
    ['sn' => 313, 'full_name' => 'Chika U. Celestina', 'class' => 'SS 1', 'section' => 'B', 'email' => 'c.celestina@abuja.bestsolution.ng'],
    ['sn' => 314, 'full_name' => 'Chukwuma O. Simeon', 'class' => 'SS 1', 'section' => 'B', 'email' => 'c.simeon@abuja.bestsolution.ng'],
    ['sn' => 315, 'full_name' => 'Fatoyinbo Samuel', 'class' => 'SS 1', 'section' => 'B', 'email' => 'f.samuel@abuja.bestsolution.ng'],
    ['sn' => 316, 'full_name' => 'Ikechukwu ThankGod', 'class' => 'SS 1', 'section' => 'B', 'email' => 'i.thankgod@abuja.bestsolution.ng'],
    ['sn' => 317, 'full_name' => 'Itodo Mary', 'class' => 'SS 1', 'section' => 'B', 'email' => 'i.mary@abuja.bestsolution.ng'],
    ['sn' => 318, 'full_name' => 'Joseph Grace', 'class' => 'SS 1', 'section' => 'B', 'email' => 'j.grace@abuja.bestsolution.ng'],
    ['sn' => 319, 'full_name' => 'Olasunkanmi Kamal', 'class' => 'SS 1', 'section' => 'B', 'email' => 'o.kamal@abuja.bestsolution.ng'],
    ['sn' => 320, 'full_name' => 'Shado Samuel', 'class' => 'SS 1', 'section' => 'B', 'email' => 's.samuel@abuja.bestsolution.ng'],
    ['sn' => 321, 'full_name' => 'Victor Mishael', 'class' => 'SS 1', 'section' => 'B', 'email' => 'v.mishael@abuja.bestsolution.ng'],
    ['sn' => 322, 'full_name' => 'Williams Ribetshak', 'class' => 'SS 1', 'section' => 'B', 'email' => 'w.ribetshak@abuja.bestsolution.ng'],
    ['sn' => 323, 'full_name' => 'Yunisa Abdul-Muminu', 'class' => 'SS 1', 'section' => 'B', 'email' => 'y.muminu@abuja.bestsolution.ng'],
    ['sn' => 324, 'full_name' => 'Adeyemi Divine', 'class' => 'SS 3', 'section' => 'A', 'email' => 'a.divine2@abuja.bestsolution.ng'],
    ['sn' => 325, 'full_name' => 'Adekunle Aliyat', 'class' => 'SS 3', 'section' => 'A', 'email' => 'a.aliyat@abuja.bestsolution.ng'],
    ['sn' => 326, 'full_name' => 'Adeniran David', 'class' => 'SS 3', 'section' => 'A', 'email' => 'a.david2@abuja.bestsolution.ng'],
    ['sn' => 327, 'full_name' => 'Ameh John', 'class' => 'SS 3', 'section' => 'A', 'email' => 'a.john@abuja.bestsolution.ng'],
    ['sn' => 328, 'full_name' => 'Buckson Juliet Salem', 'class' => 'SS 3', 'section' => 'A', 'email' => 'b.salem@abuja.bestsolution.ng'],
    ['sn' => 329, 'full_name' => 'Choke Chidimma', 'class' => 'SS 3', 'section' => 'A', 'email' => 'c.chidimma@abuja.bestsolution.ng'],
    ['sn' => 330, 'full_name' => 'Chinedu Joy. A.', 'class' => 'SS 3', 'section' => 'A', 'email' => 'c.joy@abuja.bestsolution.ng'],
    ['sn' => 331, 'full_name' => 'Daniel Precious', 'class' => 'SS 3', 'section' => 'A', 'email' => 'd.precious@abuja.bestsolution.ng'],
    ['sn' => 332, 'full_name' => 'Dela Valentina', 'class' => 'SS 3', 'section' => 'A', 'email' => 'd.valentina@abuja.bestsolution.ng'],
    ['sn' => 333, 'full_name' => 'Godwin K. Jessica', 'class' => 'SS 3', 'section' => 'A', 'email' => 'g.jessica@abuja.bestsolution.ng'],
    ['sn' => 334, 'full_name' => 'Ijaware Esther', 'class' => 'SS 3', 'section' => 'A', 'email' => 'i.esther@abuja.bestsolution.ng'],
    ['sn' => 335, 'full_name' => 'Joseph Faith', 'class' => 'SS 3', 'section' => 'A', 'email' => 'j.faith@abuja.bestsolution.ng'],
    ['sn' => 336, 'full_name' => 'Madukeve .O. Gift', 'class' => 'SS 3', 'section' => 'A', 'email' => 'm.gift@abuja.bestsolution.ng'],
    ['sn' => 337, 'full_name' => 'Nwaeze Hope', 'class' => 'SS 3', 'section' => 'A', 'email' => 'n.hope@abuja.bestsolution.ng'],
    ['sn' => 338, 'full_name' => 'Oyebamiji Israel', 'class' => 'SS 3', 'section' => 'A', 'email' => 'o.israel@abuja.bestsolution.ng'],
    ['sn' => 339, 'full_name' => 'Olanipekun Timileyin', 'class' => 'SS 3', 'section' => 'A', 'email' => 'o.timileyin@abuja.bestsolution.ng'],
    ['sn' => 340, 'full_name' => 'Olafimihun Peter', 'class' => 'SS 3', 'section' => 'A', 'email' => 'o.peter@abuja.bestsolution.ng'],
    ['sn' => 341, 'full_name' => 'Odo Joy', 'class' => 'SS 3', 'section' => 'A', 'email' => 'o.joy@abuja.bestsolution.ng'],
    ['sn' => 342, 'full_name' => 'Osewa Ibukun Esther', 'class' => 'SS 3', 'section' => 'A', 'email' => 'o.ibukun@abuja.bestsolution.ng'],
    ['sn' => 343, 'full_name' => 'Shehu Halimat', 'class' => 'SS 3', 'section' => 'A', 'email' => 's.halimat@abuja.bestsolution.ng'],
    ['sn' => 344, 'full_name' => 'Wonda Emmanuella', 'class' => 'SS 3', 'section' => 'A', 'email' => 'w.emmanuella@abuja.bestsolution.ng'],
    ['sn' => 345, 'full_name' => 'Stephen Jennifer', 'class' => 'SS 3', 'section' => 'A', 'email' => 's.jennifer@abuja.bestsolution.ng'],
    ['sn' => 346, 'full_name' => 'Raphel Joseph', 'class' => 'SS 3', 'section' => 'A', 'email' => 'r.joseph@abuja.bestsolution.ng'],
    ['sn' => 347, 'full_name' => 'Agbo Gift', 'class' => 'SS 3', 'section' => 'B', 'email' => 'a.gift@abuja.bestsolution.ng'],
    ['sn' => 348, 'full_name' => 'Adesoye Lorett', 'class' => 'SS 3', 'section' => 'B', 'email' => 'a.lorett@abuja.bestsolution.ng'],
    ['sn' => 349, 'full_name' => 'Anselm Chisom', 'class' => 'SS 3', 'section' => 'B', 'email' => 'a.chisom@abuja.bestsolution.ng'],
    ['sn' => 350, 'full_name' => 'Gado Joseph', 'class' => 'SS 3', 'section' => 'B', 'email' => 'g.joseph@abuja.bestsolution.ng'],
    ['sn' => 351, 'full_name' => 'John Vulaling', 'class' => 'SS 3', 'section' => 'B', 'email' => 'j.vulaling@abuja.bestsolution.ng'],
    ['sn' => 352, 'full_name' => 'Joshua Toluwani', 'class' => 'SS 3', 'section' => 'B', 'email' => 'j.toluwani@abuja.bestsolution.ng'],
    ['sn' => 353, 'full_name' => 'Ishola Halimat', 'class' => 'SS 3', 'section' => 'B', 'email' => 'i.halimat@abuja.bestsolution.ng'],
    ['sn' => 354, 'full_name' => 'Hungbo Oluwaseun', 'class' => 'SS 3', 'section' => 'B', 'email' => 'h.oluwaseun@abuja.bestsolution.ng'],
    ['sn' => 355, 'full_name' => 'Haidy Rofiah', 'class' => 'SS 3', 'section' => 'B', 'email' => 'h.rofiah@abuja.bestsolution.ng'],
    ['sn' => 356, 'full_name' => 'Odenusi Blessing', 'class' => 'SS 3', 'section' => 'B', 'email' => 'o.blessing2@abuja.bestsolution.ng'],
    ['sn' => 357, 'full_name' => 'Oyeleye Dorcas', 'class' => 'SS 3', 'section' => 'B', 'email' => 'o.dorcas@abuja.bestsolution.ng'],
    ['sn' => 358, 'full_name' => 'Oyewole Ruqayat', 'class' => 'SS 3', 'section' => 'B', 'email' => 'o.ruqayat@abuja.bestsolution.ng'],
    ['sn' => 359, 'full_name' => 'Romanus A. Elizabeth', 'class' => 'SS 3', 'section' => 'B', 'email' => 'r.elizabeth@abuja.bestsolution.ng'],
    ['sn' => 360, 'full_name' => 'Shedrack Emmanuel', 'class' => 'SS 3', 'section' => 'B', 'email' => 's.emmanuel@abuja.bestsolution.ng'],
    ['sn' => 361, 'full_name' => 'Timothy Goodluck', 'class' => 'SS 3', 'section' => 'B', 'email' => 't.goodluck@abuja.bestsolution.ng'],
    ['sn' => 362, 'full_name' => 'Ugwu Chinaza Lucy', 'class' => 'SS 3', 'section' => 'B', 'email' => 'u.lucy@abuja.bestsolution.ng'],
    ['sn' => 363, 'full_name' => 'Chukwu D. Chidebere', 'class' => 'SS 3', 'section' => 'B', 'email' => 'c.chidebere@abuja.bestsolution.ng'],
    ['sn' => 364, 'full_name' => 'ADEONI SAMUEL', 'class' => 'SS 2', 'section' => 'A', 'email' => 'a.samuel@abuja.bestsolution.ng'],
    ['sn' => 365, 'full_name' => 'ABACHE AWESOME', 'class' => 'SS 2', 'section' => 'A', 'email' => 'a.awesome@abuja.bestsolution.ng'],
    ['sn' => 366, 'full_name' => 'AJEH BLESSING', 'class' => 'SS 2', 'section' => 'A', 'email' => 'a.blessing@abuja.bestsolution.ng'],
    ['sn' => 367, 'full_name' => 'ABDULRAHMAN SADIQ', 'class' => 'SS 2', 'section' => 'A', 'email' => 'a.sadiq@abuja.bestsolution.ng'],
    ['sn' => 368, 'full_name' => 'AKINWALE BALIKIS', 'class' => 'SS 2', 'section' => 'A', 'email' => 'a.balikis@abuja.bestsolution.ng'],
    ['sn' => 369, 'full_name' => 'ADEDARA T. MARTINS', 'class' => 'SS 2', 'section' => 'A', 'email' => 'a.martins@abuja.bestsolution.ng'],
    ['sn' => 370, 'full_name' => 'ALADEGBAMI SAMUEL', 'class' => 'SS 2', 'section' => 'A', 'email' => 'a.samuel@abuja.bestsolution.ng'],
    ['sn' => 371, 'full_name' => 'AROGUNYO BOLUWATIFE', 'class' => 'SS 2', 'section' => 'A', 'email' => 'a.boluwatife@abuja.bestsolution.ng'],
    ['sn' => 372, 'full_name' => 'BENJAMIN JEDIDAH', 'class' => 'SS 2', 'section' => 'A', 'email' => 'b.jedidah@abuja.bestsolution.ng'],
    ['sn' => 373, 'full_name' => 'EDACHE DESTINY', 'class' => 'SS 2', 'section' => 'A', 'email' => 'e.destiny@abuja.bestsolution.ng'],
    ['sn' => 374, 'full_name' => 'FRED DESTINY', 'class' => 'SS 2', 'section' => 'A', 'email' => 'f.destiny@abuja.bestsolution.ng'],
    ['sn' => 375, 'full_name' => 'IKECHUKWU EMMANUELLA', 'class' => 'SS 2', 'section' => 'A', 'email' => 'i.emmanuella@abuja.bestsolution.ng'],
    ['sn' => 376, 'full_name' => 'IBRAHIM JUNI KHARIA', 'class' => 'SS 2', 'section' => 'A', 'email' => 'i.kharia@abuja.bestsolution.ng'],
    ['sn' => 377, 'full_name' => 'INEMETT B. FAVOUR', 'class' => 'SS 2', 'section' => 'A', 'email' => 'i.favour@abuja.bestsolution.ng'],
    ['sn' => 378, 'full_name' => 'JOSEPH KATHERINE', 'class' => 'SS 2', 'section' => 'A', 'email' => 'j.katherine@abuja.bestsolution.ng'],
    ['sn' => 379, 'full_name' => 'KAYODE ENIOLA', 'class' => 'SS 2', 'section' => 'A', 'email' => 'k.eniola@abuja.bestsolution.ng'],
    ['sn' => 380, 'full_name' => 'NATHANIEL OYINKANSOLA', 'class' => 'SS 2', 'section' => 'A', 'email' => 'n.oyinkansola@abuja.bestsolution.ng'],
    ['sn' => 381, 'full_name' => 'NWACHUKWU EMMANUEL', 'class' => 'SS 2', 'section' => 'A', 'email' => 'n.emmanuel@abuja.bestsolution.ng'],
    ['sn' => 382, 'full_name' => 'NWAEZE C. COLLINS', 'class' => 'SS 2', 'section' => 'A', 'email' => 'n.collins@abuja.bestsolution.ng'],
    ['sn' => 383, 'full_name' => 'OBINNA DAVID U.', 'class' => 'SS 2', 'section' => 'A', 'email' => 'o.david@abuja.bestsolution.ng'],
    ['sn' => 384, 'full_name' => 'EMMANUEL O. ERUOGI', 'class' => 'SS 2', 'section' => 'A', 'email' => 'e.eruogi@abuja.bestsolution.ng'],
    ['sn' => 385, 'full_name' => 'OWOEYE GODDEY', 'class' => 'SS 2', 'section' => 'A', 'email' => 'o.goddey@abuja.bestsolution.ng'],
    ['sn' => 386, 'full_name' => 'PETER PRECIOUS', 'class' => 'SS 2', 'section' => 'A', 'email' => 'p.precious@abuja.bestsolution.ng'],
    ['sn' => 387, 'full_name' => 'RAPHAEL DEBORAH', 'class' => 'SS 2', 'section' => 'A', 'email' => 'r.deborah@abuja.bestsolution.ng'],
    ['sn' => 388, 'full_name' => 'RAPHAEL PRAISE', 'class' => 'SS 2', 'section' => 'A', 'email' => 'r.praise@abuja.bestsolution.ng'],
    ['sn' => 389, 'full_name' => 'SOLOMON DIVINE', 'class' => 'SS 2', 'section' => 'A', 'email' => 's.divine@abuja.bestsolution.ng'],
    ['sn' => 390, 'full_name' => 'TUDONU ANTHONIA', 'class' => 'SS 2', 'section' => 'A', 'email' => 't.anthonia@abuja.bestsolution.ng'],
    ['sn' => 391, 'full_name' => 'UFEDO - OJO - ABU', 'class' => 'SS 2', 'section' => 'A', 'email' => 'u.abu@abuja.bestsolution.ng'],
    ['sn' => 392, 'full_name' => 'SAMUEL PEACE', 'class' => 'SS 2', 'section' => 'A', 'email' => 's.peace@abuja.bestsolution.ng'],
    ['sn' => 393, 'full_name' => 'AMSHIMA E. REJOICE', 'class' => 'SS 2', 'section' => 'A', 'email' => 'a.rejoice@abuja.bestsolution.ng'],
    ['sn' => 394, 'full_name' => 'BAMIKARERE A. FORTUNE', 'class' => 'SS 2', 'section' => 'A', 'email' => 'b.fortune@abuja.bestsolution.ng'],
    ['sn' => 395, 'full_name' => 'CHINEDU C. DESTINY', 'class' => 'SS 2', 'section' => 'A', 'email' => 'c.destiny@abuja.bestsolution.ng'],
    ['sn' => 396, 'full_name' => 'DAVID C. ANITA', 'class' => 'SS 2', 'section' => 'A', 'email' => 'd.anita@abuja.bestsolution.ng'],
    ['sn' => 397, 'full_name' => 'EGBEDE JANET', 'class' => 'SS 2', 'section' => 'B', 'email' => 'e.janet@abuja.bestsolution.ng'],
    ['sn' => 398, 'full_name' => 'GOYOL DEBORAH', 'class' => 'SS 2', 'section' => 'B', 'email' => 'g.deborah@abuja.bestsolution.ng'],
    ['sn' => 399, 'full_name' => 'JUBRIL PRECIOUS', 'class' => 'SS 2', 'section' => 'B', 'email' => 'j.precious@abuja.bestsolution.ng'],
    ['sn' => 400, 'full_name' => 'LUCKY PRECIOUS', 'class' => 'SS 2', 'section' => 'B', 'email' => 'l.precious@abuja.bestsolution.ng'],
    ['sn' => 401, 'full_name' => 'NNAMDI FAVOUR', 'class' => 'SS 2', 'section' => 'B', 'email' => 'n.favour@abuja.bestsolution.ng'],
    ['sn' => 402, 'full_name' => 'OBARO VICTOR', 'class' => 'SS 2', 'section' => 'B', 'email' => 'o.victor@abuja.bestsolution.ng'],
    ['sn' => 403, 'full_name' => 'OGBONNA CHINONYEO', 'class' => 'SS 2', 'section' => 'B', 'email' => 'o.chinonyeo@abuja.bestsolution.ng'],
    ['sn' => 404, 'full_name' => 'SAMSON DIVINE', 'class' => 'SS 2', 'section' => 'B', 'email' => 's.divine@abuja.bestsolution.ng'],
    ['sn' => 405, 'full_name' => 'SHEDRACK SARAH', 'class' => 'SS 2', 'section' => 'B', 'email' => 's.sarah@abuja.bestsolution.ng'],
    ['sn' => 406, 'full_name' => 'SIKIRU AZEEZ', 'class' => 'SS 2', 'section' => 'B', 'email' => 's.azeez@abuja.bestsolution.ng'],
    ['sn' => 407, 'full_name' => 'NAFISAT SAMOTU', 'class' => 'SS 2', 'section' => 'B', 'email' => 'n.samotu@abuja.bestsolution.ng'],
    ['sn' => 408, 'full_name' => 'MAKUOCHUKWU N. PRECIOUS', 'class' => 'SS 2', 'section' => 'B', 'email' => 'm.precious@abuja.bestsolution.ng'],
    ['sn' => 409, 'full_name' => 'Adebayo Yaseer', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'a.yaseer@abuja.bestsolution.ng'],
    ['sn' => 410, 'full_name' => 'Adebayo Ahmed', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'a.ahmed@abuja.bestsolution.ng'],
    ['sn' => 411, 'full_name' => 'Adeyemo Sarah', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'a.sarah@abuja.bestsolution.ng'],
    ['sn' => 412, 'full_name' => 'Adeyemo Thaoban', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'a.thaoban@abuja.bestsolution.ng'],
    ['sn' => 413, 'full_name' => 'Ajeh Success', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'a.success@abuja.bestsolution.ng'],
    ['sn' => 414, 'full_name' => 'Gamde Praise Wancit', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'g.praise@abuja.bestsolution.ng'],
    ['sn' => 415, 'full_name' => 'Habib Adoza Kashifulahi', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'h.adoza@abuja.bestsolution.ng'],
    ['sn' => 416, 'full_name' => 'Haruna Hasibat', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'h.hasibat@abuja.bestsolution.ng'],
    ['sn' => 417, 'full_name' => 'Ibrahim Abdulmalik', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'i.abdulmalik@abuja.bestsolution.ng'],
    ['sn' => 418, 'full_name' => 'Ibrahim Ameerat', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'i.ameerat@abuja.bestsolution.ng'],
    ['sn' => 419, 'full_name' => 'Iliyasu Praise', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'i.praise@abuja.bestsolution.ng'],
    ['sn' => 420, 'full_name' => 'Martins Jesse', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'm.jesse@abuja.bestsolution.ng'],
    ['sn' => 421, 'full_name' => 'Martins Jessica', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'm.jessica@abuja.bestsolution.ng'],
    ['sn' => 422, 'full_name' => 'Micheal K. Maranatha', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'm.maranatha@abuja.bestsolution.ng'],
    ['sn' => 423, 'full_name' => 'Nuhu Zion', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'n.zion@abuja.bestsolution.ng'],
    ['sn' => 424, 'full_name' => 'Nwoba Praise', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'n.praise@abuja.bestsolution.ng'],
    ['sn' => 425, 'full_name' => 'Oche Ene Victoria', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'o.ene@abuja.bestsolution.ng'],
    ['sn' => 426, 'full_name' => 'Odenusi Glory', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'o.glory@abuja.bestsolution.ng'],
    ['sn' => 427, 'full_name' => 'Ogah Daniel', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'o.daniel@abuja.bestsolution.ng'],
    ['sn' => 428, 'full_name' => 'Ogbonna Kingsley', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'o.kingsley@abuja.bestsolution.ng'],
    ['sn' => 429, 'full_name' => 'Omede Gochebe Dominion', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'o.gochebe@abuja.bestsolution.ng'],
    ['sn' => 430, 'full_name' => 'Onyemobi Uzochukwu', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'o.uzochukwu@abuja.bestsolution.ng'],
    ['sn' => 431, 'full_name' => 'Oussou Micheal', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'o.micheal@abuja.bestsolution.ng'],
    ['sn' => 432, 'full_name' => 'Saint-Paul O. Precious', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 's.precious@abuja.bestsolution.ng'],
    ['sn' => 433, 'full_name' => 'Shedrack Esther', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 's.esther@abuja.bestsolution.ng'],
    ['sn' => 434, 'full_name' => 'Umeanyo MaryJane', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'u.maryjane@abuja.bestsolution.ng'],
    ['sn' => 435, 'full_name' => 'Abel Goodnews', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'a.goodnews@abuja.bestsolution.ng'],
    ['sn' => 436, 'full_name' => 'Abraham Gloria', 'class' => 'Nursery 2', 'section' => 'A', 'email' => 'a.gloria@abuja.bestsolution.ng']
];

// --- LOGIC ---

$userRepository = $app->make(UserRepository::class);
$promotionRepository = $app->make(PromotionRepository::class);
$today = '2026-02-12';
$sessionId = 3; // The session ID to use for all operations
$processedEmails = [];

// --- DELETE EXISTING STUDENTS AND RELATED DATA ---
echo "Starting deletion of existing student data...\n";

try {
    // Get all student users
    $allStudentUsers = User::where('role', 'student')->get();

    foreach ($allStudentUsers as $studentUser) {
        echo "  Deleting data for student: {$studentUser->email}\n";

        // 1. Delete Wallet Transactions
        // Find wallet transactions directly linked to student fees or payments
        $studentFeeIds = StudentFee::where('student_id', $studentUser->id)->pluck('id');
        $studentPaymentIds = StudentPayment::where('student_id', $studentUser->id)->pluck('id');

        // Delete WalletTransactions that morph to StudentFee or StudentPayment
        WalletTransaction::where(function ($query) use ($studentFeeIds, $studentPaymentIds) {
            $query->where(function ($q) use ($studentFeeIds) {
                $q->where('reference_type', StudentFee::class)
                  ->whereIn('reference_id', $studentFeeIds);
            })->orWhere(function ($q) use ($studentPaymentIds) {
                $q->where('reference_type', StudentPayment::class)
                  ->whereIn('reference_id', $studentPaymentIds);
            });
        })->orWhere('wallet_id', function($query) use ($studentUser) {
            $query->select('id')
                  ->from('wallets')
                  ->where('student_id', $studentUser->id);
        })->delete();

        // 2. Delete Student Payments
        StudentPayment::where('student_id', $studentUser->id)->delete();

        // 3. Delete Student Fees
        StudentFee::where('student_id', $studentUser->id)->delete();

        // 4. Delete Marks
        Mark::where('student_id', $studentUser->id)->delete();

        // 5. Delete Promotions
        Promotion::where('student_id', $studentUser->id)->delete();

        // 6. Delete StudentParentInfo
        StudentParentInfo::where('student_id', $studentUser->id)->delete();

        // 7. Delete StudentAcademicInfo
        StudentAcademicInfo::where('student_id', $studentUser->id)->delete();

        // 8. Delete Wallet
        Wallet::where('student_id', $studentUser->id)->delete();
        
        // 9. Delete the User record itself
        $studentUser->delete();
        echo "  [SUCCESS] Deleted student and related data for: {$studentUser->email}\n";
    }
    echo "Finished deletion of existing student data.\n\n";

} catch (\Exception $e) {
    echo "  [ERROR] Failed during deletion process: " . $e->getMessage() . "\n";
    Log::error("Failed during deletion process: " . $e->getMessage());
    exit(1); // Exit if deletion fails to prevent inconsistent state
}


echo "Starting bulk student upsert process...\n";

foreach ($newStudentsData as $student) {
    $email = trim($student['email']);
    $processedEmails[] = $email;

    echo "Processing S/N: {$student['sn']} - {$student['full_name']} ({$email})\n";

    $fullName = explode(' ', $student['full_name'], 2);
    $firstName = $fullName[0];
    $lastName = $fullName[1] ?? '';

    $className = $student['class'];
    $sectionLetter = $student['section'];

    if (!isset($classMap[$className])) {
        echo "  [ERROR] Class '{$className}' not found in map.\n";
        Log::error("Class '{$className}' not found for student {$email}");
        continue;
    }
    $classId = $classMap[$className];

    $sectionId = null;
    if (isset($sectionMap[$classId])) {
        if (is_array($sectionMap[$classId])) {
            $sectionId = $sectionMap[$classId][$sectionLetter] ?? null;
        } else {
            $sectionId = $sectionMap[$classId];
        }
    }

    if (!$sectionId) {
        echo "  [ERROR] Section '{$sectionLetter}' could not be determined for class '{$className}'.\n";
        Log::error("Section '{$sectionLetter}' not found for class '{$className}' for student {$email}");
        continue;
    }

    $requestData = [
        'first_name' => $firstName, 'last_name' => $lastName, 'email' => $email,
        'gender' => '-', 'nationality' => '-', 'phone' => '-',
        'address' => '-', 'address2' => '-', 'city' => '-', 'zip' => '-',
        'photo' => null, 'birthday' => $today, 'religion' => '-', 'blood_type' => '-',
        'password' => 'password', 'father_name' => '-', 'father_phone' => '-',
        'mother_name' => '-', 'mother_phone' => '-', 'parent_address' => '-',
        'class_id' => $classId, 'section_id' => $sectionId, 'session_id' => $sessionId,
        'board_reg_no' => '-', 'id_card_number' => 'BS/ABJ/2026/' . $student['sn'],
    ];

    try {
        $existingStudent = $userRepository->findStudentByEmail($email);

        if ($existingStudent) {
            // --- UPDATE ---
            $requestData['student_id'] = $existingStudent->id;
            $userRepository->updateStudent($requestData);
            echo "  [INFO] Found existing student. Updating basic info...\n";

            // Update promotion (class/section)
            $promotionRepository->massPromotion([
                [
                    'student_id' => $existingStudent->id,
                    'session_id' => $sessionId,
                    'class_id' => $classId,
                    'section_id' => $sectionId,
                    'id_card_number' => $requestData['id_card_number'],
                ]
            ]);
            echo "  [SUCCESS] Updated student: {$student['full_name']}\n";
            Log::info("Updated student: {$student['full_name']}");
        } else {
            // --- CREATE ---
            $userRepository->createStudent($requestData);
            echo "  [SUCCESS] Created student: {$student['full_name']}\n";
            Log::info("Created student: {$student['full_name']}");
        }
    } catch (\Exception $e) {
        echo "  [ERROR] Failed to process student {$email}: " . $e->getMessage() . "\n";
        Log::error("Failed to process student {$email}: " . $e->getMessage());
    }
}

echo "\nBulk student upsert process finished.\n";





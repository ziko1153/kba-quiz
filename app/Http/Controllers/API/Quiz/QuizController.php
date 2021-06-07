<?php

namespace App\Http\Controllers\API\Quiz;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

// Resource
use App\Http\Resources\Quiz\QuizResourceCollection;
use App\Http\Resources\Quiz\QuizResource;
use App\Leaderboard;
use App\Models\Participant;
// Model
use App\Models\Quiz;
use JWTAuth;
use Tymon\JWTAuth\Facades\JWTAuth as FacadesJWTAuth;

class QuizController extends Controller
{


    public function getQuizzes(){

        $user = JWTAuth::user();

        if($user->quizStart == 1) {
            return response()->json(['message' => 'দুঃখিত আপনি ইতিমধ্যে কুইজটি খেলে ফেলেছেন। দ্বিতীয়বার কুইজ খেলার সুযোগ নেই'],420);
        }

        return QuizResourceCollection::collection(Quiz::with('questions.answers')->orderBy('id', 'desc')->get());
    }

    public function startQuize(Request $request) {
        $user = JWTAuth::user();

        $user = Participant::find($user->id);

        if($user->quizStart == 1)  return response()->json('Already Started');

        $user->quizStart = 1; 
        $user->quizStartTime = $request->date;

        if($user->save()) {
            return response()->json('success');
        }
      
    }


    public function getLeaderboard() {

        $Leaderboard = Leaderboard::orderBy('correctAnswer','DESC')->orderBy('finishTime','ASC')->limit(10)->get();

        $finalResult = [];

        foreach($Leaderboard as $result) {
            $finalResult[] = [
                'participant' => $result->participant->fullName,
                'correctAnswer' => $result->correctAnswer,
                'time' => $result->finishTime,
            ];
        }

        return response()->json(['result' => $finalResult]);
    }
}
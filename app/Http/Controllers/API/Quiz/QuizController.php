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
use App\QuizStart;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use Tymon\JWTAuth\Facades\JWTAuth as FacadesJWTAuth;

class QuizController extends Controller
{


    public function getQuizzes(Request $request){

        $language = $request->header('language','bn');


        $user = JWTAuth::user();

        $leaderboard = Leaderboard::where('participantId',$user->id)->first();

        if($leaderboard) {

            return response()->json(['message' =>  ($language == 'bn')? 'দুঃখিত আপনি ইতিমধ্যে কুইজটি   খেলে ফেলেছেন। পুনরায় কুইজ খেলার সুযোগ নেই' : 'Sorry Already Quiz Played .You can not play this quiz again'],420);
        }

        if($user->quizStart == 2 || $user->quizStart>2) {
            return response()->json(['message' =>  ($language == 'bn')? 'দুঃখিত আপনি ইতিমধ্যে কুইজটি  দুইবার খেলে ফেলেছেন। পুনরায় কুইজ খেলার সুযোগ নেই' : 'Sorry Already Played 2 times.You can not play this quiz again'],420);
        }

        return QuizResourceCollection::collection(Quiz::with('questions.answers')->orderBy('id', 'desc')->get());
    }

    public function startQuize(Request $request) {
        $user = JWTAuth::user();

        $user = Participant::find($user->id);

        $leaderboard = Leaderboard::where('participantId',$user->id)->first();

        if($leaderboard) {

            return response()->json(['error' => 'Already Quiz Played'],422);
        }

        if($user->quizStart == 2 || $user->quizStart >2 )  return response()->json(['error' => 'Already Quiz Played'],422);

        $user->quizStart =  $user->quizStart + 1; 
        $user->quizStartTime = $request->date;

        if($user->save()) {

            $quizeStart = new QuizStart;

            $quizeStart->participantId = $user->id;
            $quizeStart->quizStartTime = date('Y-m-d H:i:s',$request->date/1000);

            $quizeStart->save();
            
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
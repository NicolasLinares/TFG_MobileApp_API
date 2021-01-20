<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

use App\Models\Transcript;
use App\Models\Audio;

use App\Http\Controllers\INVOXMDController;

// Esta clase permite controlar todas las peticiones HTTP de INVOX MEDICAL
class TranscriptionController extends Controller
{
    function getTranscript($uid, Request $request)
    {
        if ($request->isJson()) {

            $doctor = Auth::id();
            // Obtiene primero el id del audio asociado
            $id_audio = Audio::select('id')->where([['doctor', '=', $doctor], ['uid', '=', $uid]])->first();
            // Obtiene la transcripción asociada al id del audio
            $transcription = Transcript::where('audio', $id_audio['id'])->first();

            // Si no está completada se procede a recuperarla del servicio de transcripción
            if ($transcription['status'] !== 'Completada') {

                // INVOXMD - SERVICIO DE TRANSCRIPCIÓN
                // -----------------------------------------------------------------
                // Se recupera la transcripción de InvoxMD
                $invoxmd_service = new INVOXMDController();
                $response = $invoxmd_service->getTranscriptINVOXMD($transcription['id']);

                // BASE DE DATOS
                // -----------------------------------------------------------------
                $info = $response['Info'];
                $transcription['status'] = $info['Status'];
                $transcription['progress'] = strval($info['Progress']);
                $transcription['start_date'] = $info['StartDate'];
                $transcription['end_date'] = $info['EndDate'];
                $transcription['text'] = $response['Text'];
                $transcription->save();
            }

            return response()->json($transcription, 200);
        } else {
            return response()->json(['error' => 'Usuario no autorizado.'], 401);
        }
    }

}

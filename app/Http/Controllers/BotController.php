<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\Telegram;
use App\Models\BotUser;
use App\Models\Image;

class BotController extends Controller
{
    use Telegram;

    public $botDev = 1332150757;

    public function index() 
    {
        $update = json_decode(file_get_contents('php://input'));

        if (isset($update->message)) {
            $message = $update->message;
            $chat = $message->chat;
            $chat_id = $chat->id;
            $chat_type = $chat->type;
            $from = $message->from;
            $first_name = $from->first_name;
            $last_name = $from->last_name ?? null;
            $user = BotUser::where('user_id', $chat_id)->first();
            if(!$user){
                $user = BotUser::create([
                    'user_id' => $chat_id,
                    'step' => 'start'
                ]);
            }

            if (isset($message->text)) {
                $text = $message->text;

                if ($chat_type == 'private') {
                    if ($text == '/start') {
                        $text = "Salom $first_name!";

                        $user->update([
                            'step' => 'start'
                        ]);

                        $this->sendMessage('sendMessage', [
                            'chat_id' => $chat_id,
                            'text' => $text,
                            'parse_mode' => 'markdown'
                        ]);
                    } else {
                        if($chat_id == $this->botDev){
                            if ($text == '/upload') {
                                $this->sendMessage('sendMessage', [
                                    'chat_id' => $chat_id,
                                    'text' => 'Rasm yuboring rasm nomi captionda bolsin',
                                    'parse_mode' => 'markdown'
                                ]);
        
                                $user->update([
                                    'step' => 'upload'
                                ]);
                            } else {
                                $image = Image::where('name', $text)->first();
    
                                if(!$image){
                                    $this->sendMessage('sendMessage', [
                                        'chat_id' => $chat_id,
                                        'text' => 'Rasm topilmadi 🤷‍♂️',
                                    ]);
                                } else {
                                    $this->sendMessage('sendPhoto', [
                                        'chat_id' => $chat_id,
                                        'photo' => $image->file_id,
                                        'caption' => $image->name
                                    ]);
                                }
                            }
                        } else {
                            $image = Image::where('name', $text)->first();
    
                            if(!$image){
                                $this->sendMessage('sendMessage', [
                                    'chat_id' => $chat_id,
                                    'text' => 'Rasm topilmadi 🤷‍♂️',
                                ]);
                            } else {
                                $this->sendMessage('sendPhoto', [
                                    'chat_id' => $chat_id,
                                    'photo' => $image->file_id,
                                    'caption' => $image->name
                                ]);
                            }
                        }
                    }
                }
            }
            if (isset($message->photo) && $chat_id == $this->botDev) {
                if(isset($message->caption)){
                    $imageName = $message->caption;
                    $lastImage = end($message->photo);
                    $fileID = $lastImage->file_id;

                    $existImage = Image::where('name', $imageName)->first();
                    if($existImage){
                        $this->sendMessage('sendMessage', [
                            'chat_id' => $chat_id,
                            'text' => 'Ushbu nomdagi rasm allaqachon mavjud! 🤷‍♂️',
                        ]);
                        return;
                    }
                    
                    // $this->jjson($lastImage);
                    $this->sendMessage('sendMessage', [
                        'chat_id' => $chat_id,
                        'text' => 'Rasm muvaffaqiyatli yuklandi ✅',
                    ]);

                    Image::create([
                        'name' => $imageName, 
                        'file_id' => $fileID
                    ]);

                    $user->update([
                        'step' => 'uploaded'
                    ]);
                } else {
                    $this->sendMessage('sendMessage', [
                        'chat_id' => $chat_id,
                        'text' => 'Format xato',
                    ]);   
                }
            }
        }
    }

    public function jjson($update)
    {
        $longText = json_encode($update, JSON_PRETTY_PRINT);
        if (strlen($longText) > 4096){
            $textChunks = str_split($longText, 4096);
            foreach ($textChunks as $textChunk){
                $this->sendMessage('sendMessage', [
                    'chat_id' => $this->botDev,
                    'text' => $textChunk,
                ]);
            }
        } else {
            $this->sendMessage('sendMessage', [
                'chat_id' => $this->botDev,
                'text' => $longText,
            ]);
        }
    }
}

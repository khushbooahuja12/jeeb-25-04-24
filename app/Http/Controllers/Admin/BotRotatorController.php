<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\CoreApiController;
use Illuminate\Support\Facades\Http;
use App\Model\BotCommand;

class BotRotatorController extends CoreApiController
{
    public function index(Request $request)
    {
        $filter = $request->query('filter');
        $identifier = $request->query('identifier');

        $query = BotCommand::query();

        if (!empty($filter)) {
            $query->where('command', 'like', '%' . $filter . '%');
        }

        if (!empty($identifier)) {
            $query->where('identifier', $identifier);
        }

        $commands = $query->orderBy('id','desc')->paginate(50);

        $commands->appends(['filter' => $filter, 'identifier' => $identifier]);

        $identifiers = BotCommand::select('identifier')->groupBy('identifier')->get();

        return view('admin.bot_rotator.index', [
            'commands' => $commands,
            'identifiers' => $identifiers,
            'identifier' => $identifier,
            'filter' => $filter
        ]);
    }

    public function rotate(Request $request)
    {
        $identifier = $request->input('identifier');
        $commands = BotCommand::where('identifier', '=', $identifier)->get();

        $api_url = 'http://203.161.62.27:8000/api/v1/boat/completions';
        $headers = [
            'Content-Type: application/json',
            'User-Agent: Insomnia/2023.5.0-beta.8',
            'accept: application/json'
        ];

        for ($i = 1; $i <= 6; $i++) {
            foreach ($commands as $value) {
                $request_data = ["text" => $value->command];

                $curl = curl_init($api_url);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($request_data));
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);

                $response_json = json_decode($response);

                if ($response_json && isset($response_json->identifier)) {
                    $statusKey = "status_$i";

                    if ($i == 1 && $response_json->identifier == $identifier) {
                        $value->update([$statusKey => 'TRUE']);
                    } elseif ($i == 1 && $response_json->identifier != $identifier) {
                        $value->update([$statusKey => 'FALSE']);
                    } elseif ($i > 1 && $value->{"status_" . ($i - 1)} == 'TRUE') {
                        $value->update([$statusKey => $response_json->identifier == $identifier ? 'TRUE' : 'NA']);
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'Bot Rotated Successfully');
    }

    public function create(Request $request)
    {
        return view('admin.bot_rotator.create');
    }

    public function store(Request $request)
    {
        $insert_arr = [
            'identifier' => $request->input('identifier'),
            'command' => $request->input('command'),
        ];

        $exist = BotCommand::where(['identifier' => $request->input('identifier'), 'command' => $request->input('command')])->first();
        if ($exist) {
            return redirect('admin/bot_rotator/create')->with('error', 'Command already exist');
        }
        $create = BotCommand::create($insert_arr);
        if ($create) {
            return redirect('admin/bot_rotator/create')->with('success', 'Product added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding Product');
    }

    public function upload_csv(Request $request)
    {
        $path = "/command_files/";
        $file = $this->uploadFile($request, 'command_file', $path);

        $commands = csvToArray(public_path('/command_files/') . $file);

        if($commands){
            foreach ($commands as $key => $value) {
                $insert_arr = [
                    'command' => trim($value[0]),
                    'identifier' => trim($value[1])
                ];

                $create = BotCommand::create($insert_arr);
            }
            return redirect('admin/bot_rotator/create')->with('success', 'File uploaded successfully');
        }

        return back()->withInput()->with('error', 'Error while adding Product');
        
    }
}

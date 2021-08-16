<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use MailerLiteApi\MailerLite;
use Monarobase\CountryList\CountryListFacade;

class SubscriberController extends Controller
{
    /**
     * @var \MailerLiteApi\Api\Subscribers
     */
    protected $subscribersApi;

    /**
     * Subscriber Controller construct.
     *
     */
    public function __construct()
    {
        $apiKeySetting = Settings::where('key', 'MAILERLITE_API_KEY')->first();
        if (!is_null($apiKeySetting)) {
            $this->subscribersApi = (new MailerLite($apiKeySetting->value))->subscribers();
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $this->subscribersApi
                ->offset($request->get('start'))
                ->limit($request->get('length'));

            $search = $request->get('search');
            if (!is_null($search) && isset($search['value'])) {
                $subscribers = $this->subscribersApi->search($search['value']);
                $count = count((array) $subscribers);
            } else {
                $subscribers = $this->subscribersApi->get()->toArray();
                $count = $this->subscribersApi->count()->count;
            }

            $data = [];
            foreach ($subscribers as $subscriber) {
                $fields = $subscriber->fields;
                $country = $this->getFieldValue($fields, 'country');
                $dateSubscribe = $subscriber->date_created; //date_subcribe is null, using date_created instead
                $data[] = [
                    'id' => $subscriber->id,
                    'name' => $subscriber->name,
                    'email' => $subscriber->email,
                    'country' => $country,
                    'subscribe_date' => Carbon::createFromFormat('Y-m-d H:i:s', $dateSubscribe)->format('d-m-Y'),
                    'subscribe_time' => Carbon::createFromFormat('Y-m-d H:i:s', $dateSubscribe)->format('H:i:s'),
                    'date_subscribe' => $dateSubscribe,
                ];
            }

            return [
                'draw' => $request->get('draw'),
                'data' => $data,
                'recordsTotal' => $count,
                'recordsFiltered' => $count,
            ];
        }

        $countries = CountryListFacade::getList();

        return view('subscribers.list', [
            'countries' => $countries,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $fail($attribute . ' is invalid.');
                    }
                },
            ],
            'country' => 'required',
        ]);

        $data = [
            'name' => $request->post('name'),
            'email' => $request->post('email'),
            'fields' => [
                'country' => $request->post('country'),
            ],
        ];

        $result = $this->subscribersApi->create($data);
        $checkError = $this->checkError($result);
        if ($checkError !== null) {
            return response()->json(
                $checkError,
                400
            );
        } else {
            return ['success' => true];
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $subscriberDetails = $this->subscribersApi->find($id);
        $checkError = $this->checkError($subscriberDetails);
        if ($checkError !== null) {
            return response()->json(
                $checkError,
                404
            );
        } else {
            $country = $this->getFieldValue($subscriberDetails->fields, 'country');
            return [
                'name' => $subscriberDetails->name,
                'email' => $subscriberDetails->email,
                'country' => $country,
            ];
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'country' => 'required',
        ]);

        $data = [
            'name' => $request->post('name'),
            'fields' => [
                'country' => $request->post('country'),
            ],
        ];

        $updateResult = $this->subscribersApi->update($id, $data);
        $checkError = $this->checkError($updateResult);
        if ($checkError !== null) {
            return response()->json(
                $checkError,
                400
            );
        } else {
            return ['success' => true];
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deleteResult = $this->subscribersApi->delete($id);
        $checkError = $this->checkError($deleteResult);
        if ($checkError !== null) {
            return response()->json(
                $checkError,
                404
            );
        } else {
            return ['success' => true];
        }

    }

    /**
     * Get field value for a key from a subscriber data
     *
     * @param array $fields
     * @param string $key
     * @return string
     */
    public function getFieldValue($fields, $key)
    {
        foreach ($fields as $field) {
            if ($field->key == $key) {
                return $field->value;
            }
        }

        return '-';
    }

    /**
     * Check error in MailerLite API response
     *
     * @param object $result
     * @return array|null
     */
    protected function checkError($result)
    {
        if ($result !== null && property_exists($result, 'error')) {
            return ['errors' => ['message' => $result->error->message]];
        }

        return null;
    }
}

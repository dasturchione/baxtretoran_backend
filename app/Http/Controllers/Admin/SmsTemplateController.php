<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SmsTemplate;
use Illuminate\Http\Request;

class SmsTemplateController extends Controller
{
    public function index()
    {
        return SmsTemplate::all();
    }

    public function show(SmsTemplate $smsTemplate)
    {
        return $smsTemplate;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key'     => 'required|string|unique:sms_templates,key',
            'name'    => 'required|string',
            'content' => 'required|string',
        ]);

        $template = SmsTemplate::create($validated);

        return response()->json($template, 201);
    }

    public function update(Request $request, SmsTemplate $smsTemplate)
    {
        $validated = $request->validate([
            'key'     => 'required|string|unique:sms_templates,key,' . $smsTemplate->id,
            'name'    => 'required|string',
            'content' => 'required|string',
        ]);

        $smsTemplate->update($validated);

        return response()->json($smsTemplate);
    }

    public function destroy(SmsTemplate $smsTemplate)
    {
        $smsTemplate->delete();

        return response()->json(null, 204);
    }
}

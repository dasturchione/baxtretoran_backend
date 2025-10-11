<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SmsTemplateResource;
use App\Models\SmsTemplate;
use App\Services\CrudService;
use Illuminate\Http\Request;

class SmsTemplateController extends Controller
{
    private $smstemplateModel;
    private $crudService;

    public function __construct(SmsTemplate $smstemplateModel, CrudService $crudService)
    {
        $this->smstemplateModel = $smstemplateModel;
        $this->crudService = $crudService;
    }

    public function index(Request $request)
    {
        $perPage = $request->query('paginate');
        $query = $this->smstemplateModel;

        if ($perPage) {
            $smstemplate = $query->paginate($perPage);
        } else {
            $smstemplate = $query->get();
        }
        return SmsTemplateResource::collection($smstemplate);
    }

    public function show($id)
    {
        $smsTemplate = $this->smstemplateModel->findOrFail($id);
        return new SmsTemplateResource($smsTemplate);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key'     => 'required|string|unique:sms_templates,key',
            'name'    => 'required|string',
            'content' => 'required|string',
        ]);

        $template = $this->crudService->CREATE_OR_UPDATE($this->smstemplateModel, $validated, [], null);

        return response()->json($template, 201);
    }

    public function update(Request $request, $id)
    {
        $smsTemplate = $this->smstemplateModel->findOrFail($id);

        $validated = $request->validate([
            'key'     => 'required|string|unique:sms_templates,key,' . $smsTemplate->id,
            'name'    => 'required|string',
            'content' => 'required|string',
        ]);

        $template = $this->crudService->CREATE_OR_UPDATE(
            $this->smstemplateModel,
            $validated,
            [],
            $id
        );

        return response()->json($template);
    }


    public function destroy(SmsTemplate $smsTemplate)
    {
        $smsTemplate->delete();

        return response()->json(null, 204);
    }
}

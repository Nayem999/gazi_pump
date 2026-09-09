<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Authorization here is the tally.agent middleware (a machine credential,
 * not a human user/policy) — same shape as SubmitTallySyncResultRequest.
 */
class SubmitTallyStockSyncRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Only the closing position and the item are ever guaranteed.
        //
        // Godown is nullable because single-depot mode carries none: the
        // live customer's Tally cannot serve item x godown quantities, so
        // SFA attributes those rows to `sfa.tally.default_depot_code` (see
        // TallyStockSyncService). The movement columns are nullable for the
        // same reason — Stock Summary reports a closing position, not
        // movement, and requiring them would force the agent to invent
        // zeroes that the service then couldn't distinguish from real ones.
        return [
            'rows' => ['required', 'array'],
            'rows.*.stock_item_name' => ['required', 'string'],
            'rows.*.godown_name' => ['nullable', 'string'],
            'rows.*.stock_item_tally_guid' => ['nullable', 'string', 'max:255'],
            'rows.*.godown_tally_guid' => ['nullable', 'string', 'max:255'],
            'rows.*.opening_qty' => ['nullable', 'numeric'],
            'rows.*.in_qty' => ['nullable', 'numeric'],
            'rows.*.out_qty' => ['nullable', 'numeric'],
            'rows.*.closing_qty' => ['required', 'numeric'],
        ];
    }
}

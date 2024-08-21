<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Position;

class FormationString implements DataAwareRule, ValidationRule
{
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;
 
        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $allPositions = Position::all()
            ->keyBy('position')
            ->toArray();

        $rowCounts = str_split($this->data['name'], 1);

        $noWhitespace = str_replace(' ', '', $value);

        preg_match_all('/(^.+$)/m', $noWhitespace, $matches);

        $rowFormations = array_reverse($matches[0]);

        foreach ($rowCounts as $i => $count)
        {
            $rowPositions = explode(',', $rowFormations[$i]);

            if ($count != count($rowPositions))
            {
                $fail("Part of the name ({$count}) does not match the formation ({$rowFormations[$i]}).");
            }

            foreach ($rowPositions as $pos)
            {
                $pos = strtoupper(trim($pos));
                if (!isset($allPositions[$pos]))
                {
                    $fail("[{$pos}] is not a valid position.");
                }
            }
        }
    }
}

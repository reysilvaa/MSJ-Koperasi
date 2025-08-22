<?php

if (!function_exists('getEnum')) {
    /**
     * Get enum options
     * 
     * @param string $idenum
     * @param bool $activeOnly
     * @return \Illuminate\Support\Collection
     */
    function getEnum($idenum, $activeOnly = true)
    {
        $query = DB::table('sys_enum')->where('idenum', $idenum);
        
        if ($activeOnly) {
            $query->where('isactive', '1');
        }
        
        return $query->select('value', 'name')->orderBy('name')->get();
    }
}

if (!function_exists('getEnumArray')) {
    /**
     * Get enum as array (value => name)
     * 
     * @param string $idenum
     * @param bool $activeOnly
     * @return array
     */
    function getEnumArray($idenum, $activeOnly = true)
    {
        $query = DB::table('sys_enum')->where('idenum', $idenum);
        
        if ($activeOnly) {
            $query->where('isactive', '1');
        }
        
        return $query->orderBy('name')->pluck('name', 'value')->toArray();
    }
}

if (!function_exists('getEnumLabel')) {
    /**
     * Get enum label by value
     * 
     * @param string $idenum
     * @param string $value
     * @return string|null
     */
    function getEnumLabel($idenum, $value)
    {
        if (empty($value)) {
            return null;
        }

        return DB::table('sys_enum')
            ->where('idenum', $idenum)
            ->where('value', $value)
            ->value('name');
    }
}

if (!function_exists('getEnumForSelect')) {
    /**
     * Get enum for select dropdown with empty option
     * 
     * @param string $idenum
     * @param string $emptyLabel
     * @param bool $activeOnly
     * @return array
     */
    function getEnumForSelect($idenum, $emptyLabel = 'Pilih...', $activeOnly = true)
    {
        $options = getEnumArray($idenum, $activeOnly);
        
        if ($emptyLabel) {
            return ['' => $emptyLabel] + $options;
        }
        
        return $options;
    }
}

if (!function_exists('getEnumValues')) {
    /**
     * Get enum values only (for validation)
     * 
     * @param string $idenum
     * @param bool $activeOnly
     * @return array
     */
    function getEnumValues($idenum, $activeOnly = true)
    {
        $query = DB::table('sys_enum')->where('idenum', $idenum);
        
        if ($activeOnly) {
            $query->where('isactive', '1');
        }
        
        return $query->pluck('value')->toArray();
    }
}

if (!function_exists('enumValidationRule')) {
    /**
     * Get validation rule for enum
     * 
     * @param string $idenum
     * @param bool $activeOnly
     * @return string
     */
    function enumValidationRule($idenum, $activeOnly = true)
    {
        $values = getEnumValues($idenum, $activeOnly);
        return 'in:' . implode(',', $values);
    }
}
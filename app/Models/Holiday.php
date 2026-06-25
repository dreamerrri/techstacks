<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'type',
        'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Scope: active holidays only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: regular holidays
     */
    public function scopeRegular($query)
    {
        return $query->where('type', 'regular');
    }

    /**
     * Scope: special holidays
     */
    public function scopeSpecial($query)
    {
        return $query->where('type', 'special');
    }

    /**
     * Check if a specific date is a holiday
     *
     * @param string|Carbon $date
     * @return bool
     */
    public static function isHoliday($date): bool
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        
        return self::active()
            ->where('date', $date->toDateString())
            ->exists();
    }

    /**
     * Get holiday for a specific date
     *
     * @param string|Carbon $date
     * @return Holiday|null
     */
    public static function getHolidayForDate($date): ?Holiday
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        
        return self::active()
            ->where('date', $date->toDateString())
            ->first();
    }

    /**
     * Get holiday type for a specific date
     *
     * @param string|Carbon $date
     * @return string|null Returns 'regular', 'special', or null if not a holiday
     */
    public static function getHolidayType($date): ?string
    {
        $holiday = self::getHolidayForDate($date);
        
        return $holiday ? $holiday->type : null;
    }

    /**
     * Check if a date is a regular holiday
     *
     * @param string|Carbon $date
     * @return bool
     */
    public static function isRegularHoliday($date): bool
    {
        return self::getHolidayType($date) === 'regular';
    }

    /**
     * Check if a date is a special holiday
     *
     * @param string|Carbon $date
     * @return bool
     */
    public static function isSpecialHoliday($date): bool
    {
        return self::getHolidayType($date) === 'special';
    }

    /**
     * Get all holidays for a date range
     *
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getHolidaysInRange($startDate, $endDate)
    {
        $startDate = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $endDate = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);
        
        return self::active()
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date')
            ->get();
    }

    /**
     * Get upcoming holidays (from today onwards)
     *
     * @param int $limit Number of holidays to return
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getUpcomingHolidays($limit = 10)
    {
        return self::active()
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->limit($limit)
            ->get();
    }
}

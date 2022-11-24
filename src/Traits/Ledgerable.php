<?php
/**
 * Created by PhpStorm.
 * User: andre
 * Date: 2017-06-24
 * Time: 12:26 PM.
 */

namespace DanDoingDev\Ledger\Traits;

use DanDoingDev\Ledger\Facades\Ledger;
use DanDoingDev\Ledger\LedgerEntry;
use Illuminate\Database\Eloquent\Model;

trait Ledgerable
{
    /**
     * Get all of the entity's ledger entries.
     *
     * @return mixed
     */
    public function entries()
    {
        return $this->morphMany(LedgerEntry::class, 'ledgerable')->orderBy('id', 'desc');
    }

    /**
     * Get all of the entity's ledger debit entries.
     *
     * @return mixed
     */
    public function debits()
    {
        return $this->entries()->where('credit', '=', false);
    }

    /**
     * Get all of the entity's ledger credit entries.
     *
     * @return mixed
     */
    public function credits()
    {
        return $this->entries()->where('credit', '=', true);
    }

    /**
     * topup entity.
     *
     * @param mixed $from
     * @param mixed $amount
     * @param mixed $reason
     * @param mixed $currency
     *
     * @return mixed
     */
    public function topUp($amount, $currency = 'USD', $reason = null)
    {
        return Ledger::topUp($this, $amount, $currency, $reason);
    }

    /**
     * debit entity.
     *
     * @param mixed $from
     * @param mixed $amount
     * @param mixed $reason
     * @param mixed $currency
     *
     * @return mixed
     */
    public function debit(Model $from, $amount, $currency = 'USD', $reason = null)
    {
        return Ledger::debit($this, $from, $amount, $currency, $reason);
    }

    /**
     * credit entity.
     *
     * @param mixed $currency
     * @param mixed $to
     * @param mixed $amount
     * @param mixed $reason
     *
     * @return mixed
     */
    public function credit(Model $to, $amount, $currency = 'USD', $reason = null)
    {
        return Ledger::credit($this, $to, $amount, $currency, $reason);
    }

    /**
     * get entity's balance.
     *
     * @return mixed
     */
    public function balance()
    {
        return Ledger::balance($this);
    }

    /**
     * transfer amount from entity to each recipient.
     *
     * @param mixed $currency
     * @param mixed $to
     * @param mixed $amount
     * @param mixed $reason
     *
     * @return mixed
     */
    public function transfer(Model $to, $amount, $currency = 'USD', $reason = null)
    {
        return Ledger::transfer($this, $to, $amount, $currency, $reason);
    }
}

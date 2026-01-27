<?php

namespace Modules\Account\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceProduct;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Account\Entities\AccountUtility;
use Modules\Account\Entities\BankAccount;
use Modules\Account\Entities\Bill;
use Modules\Account\Entities\BillProduct;
use Modules\Account\Entities\Customer;
use Modules\Account\Entities\Payment;
use Modules\Account\Entities\Revenue;
use Modules\Account\Entities\StockReport;
use Modules\Account\Entities\Vender;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    public function accountStatement(Request $request)
    {
        if(Auth::user()->isAbleTo('report statement manage'))
        {

            $filter['account']             = __('All');
            $filter['type']                = __('Revenue');
            $reportData['revenues']        = '';
            $reportData['payments']        = '';
            $reportData['revenueAccounts'] = '';
            $reportData['paymentAccounts'] = '';

            $account = BankAccount::where('workspace', '=', getActiveWorkSpace())->get()->pluck('holder_name', 'id');

            $types = [
                'revenue' => __('Revenue'),
                'payment' => __('Payment'),
            ];
            if(!isset($request->type))
            {
                $request->type = 'payment';
            }

            if($request->type == 'revenue' || !isset($request->type))
            {

                $revenueAccounts = Revenue::select('bank_accounts.id', 'bank_accounts.holder_name', 'bank_accounts.bank_name')->leftjoin('bank_accounts', 'revenues.account_id', '=', 'bank_accounts.id')->groupBy('revenues.account_id')->selectRaw('sum(amount) as total')->where('revenues.created_by', '=', creatorId());

                $revenues = Revenue::where('revenues.created_by', '=', creatorId())->orderBy('id', 'desc');
            }

            if($request->type == 'payment')
            {
                $paymentAccounts = Payment::select('bank_accounts.id', 'bank_accounts.holder_name', 'bank_accounts.bank_name')->leftjoin('bank_accounts', 'payments.account_id', '=', 'bank_accounts.id')->groupBy('payments.account_id')->selectRaw('sum(amount) as total')->where('payments.workspace', '=', getActiveWorkSpace());
                $payments = Payment::where('payments.workspace', '=', getActiveWorkSpace())->orderBy('id', 'desc');
            }


            if(!empty($request->start_month) && !empty($request->end_month))
            {
                $start = strtotime($request->start_month);
                $end   = strtotime($request->end_month);
            }
            else
            {
                $start = strtotime(date('Y-m'));
                $end   = strtotime(date('Y-m', strtotime("-5 month")));
            }
            $currentdate = $start;
            while($currentdate <= $end)
            {
                $data['month'] = date('m', $currentdate);
                $data['year']  = date('Y', $currentdate);

                if($request->type == 'payment')
                {
                    $paymentAccounts->Orwhere(
                        function ($query) use ($data){
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                            $query->where('payments.workspace', '=',getActiveWorkSpace());
                        }
                    );
                }
                if($request->type == 'revenue')
                {
                    $revenueAccounts->Orwhere(
                        function ($query) use ($data){
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                            $query->where('revenues.workspace', '=',getActiveWorkSpace());
                        }
                    );
                }


                $currentdate = strtotime('+1 month', $currentdate);
            }
            if(!empty($request->account))
            {
                if($request->type == 'revenue' )
                {
                    $revenues->where('account_id', $request->account);
                    $revenues->where('revenues.workspace', '=', getActiveWorkSpace());
                    $revenueAccounts->where('account_id', $request->account);
                    $revenueAccounts->where('revenues.workspace', '=', getActiveWorkSpace());
                }

                if($request->type == 'payment')
                {
                    $payments->where('account_id', $request->account);
                    $payments->where('payments.workspace', '=',getActiveWorkSpace());

                    $paymentAccounts->where('account_id', $request->account);
                    $paymentAccounts->where('payments.workspace', '=',getActiveWorkSpace());
                }


                $bankAccount       = BankAccount::find($request->account);
                $filter['account'] = !empty($bankAccount) ? $bankAccount->holder_name . ' - ' . $bankAccount->bank_name : '';
                if($bankAccount->holder_name == 'Cash')
                {
                    $filter['account'] = 'Cash';
                }
            }

            if($request->type == 'revenue' )
            {
                $reportData['revenues'] = $revenues->get();

                $revenueAccounts->where('revenues.workspace', '=',getActiveWorkSpace());
                $reportData['revenueAccounts'] = $revenueAccounts->get();
                $filter['type']                = __('Revenue');


            }

            if($request->type == 'payment')
            {
                $reportData['payments'] = $payments->get();

                $paymentAccounts->where('payments.workspace', '=',getActiveWorkSpace());
                $reportData['paymentAccounts'] = $paymentAccounts->get();
                $filter['type']                = __('Payment');
            }


            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange']   = date('M-Y', $end);

            return view('account::report.statement_report', compact('reportData', 'account', 'types', 'filter'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    public function incomeSummary(Request $request)
    {

            if(\Auth::user()->isAbleTo('report income manage'))
            {
                $account = BankAccount::where('workspace', '=', getActiveWorkSpace())->get()->pluck('holder_name', 'id');

                $customer = Customer::where('workspace', '=', getActiveWorkSpace())->get()->pluck('name', 'id');
                $category = [];
                if(module_is_active('ProductService'))
                {
                    $category = \Modules\ProductService\Entities\Category::where('created_by', '=', creatorId())->where('workspace_id', '=', getActiveWorkSpace())->where('type', '=', 1)->get()->pluck('name', 'id');
                }

                $data['monthList']  = $month = $this->yearMonth();
                $data['yearList']   = $this->yearList();
                $filter['category'] = __('All');
                $filter['customer'] = __('All');


                if(isset($request->year))
                {
                    $year = $request->year;
                }
                else
                {
                    $year = date('Y');
                }
                $data['currentYear'] = $year;

                // ------------------------------REVENUE INCOME-----------------------------------
                $incomes = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id')->leftjoin('categories', 'revenues.category_id', '=', 'categories.id')->where('categories.type', '=', 1);
                $incomes->where('revenues.workspace', '=', getActiveWorkSpace());
                $incomes->whereRAW('YEAR(date) =?', [$year]);
                if(!empty($request->category))
                {
                    $incomes->where('category_id', '=', $request->category);
                    $cat = [];
                    if(module_is_active('ProductService'))
                    {
                        $cat                = \Modules\ProductService\Entities\Category::find($request->category);
                    }
                        $filter['category'] = !empty($cat) ? $cat->name : '';
                }

                if(!empty($request->customer))
                {
                    $incomes->where('customer_id', '=', $request->customer);
                    $cust               = Customer::find($request->customer);
                    $filter['customer'] = !empty($cust) ? $cust->name : '';
                }
                $incomes->groupBy('month', 'year', 'category_id');
                $incomes = $incomes->get();

                $tmpArray = [];
                foreach($incomes as $income)
                {
                    $tmpArray[$income->category_id][$income->month] = $income->amount;
                }
                $array = [];
                foreach($tmpArray as $cat_id => $record)
                {
                    $tmp             = [];
                    $tmp['category'] = [];
                    if(module_is_active('ProductService'))
                    {
                        $tmp['category'] = !empty(\Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()) ? \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                    }
                    $tmp['data']     = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $tmp['data'][$i] = array_key_exists($i, $record) ? $record[$i] : 0;
                    }
                    $array[] = $tmp;
                }


                $incomesData = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year');
                $incomesData->where('revenues.workspace', '=', getActiveWorkSpace());
                $incomesData->whereRAW('YEAR(date) =?', [$year]);

                if(!empty($request->category))
                {
                    $incomesData->where('category_id', '=', $request->category);
                }
                if(!empty($request->customer))
                {
                    $incomesData->where('customer_id', '=', $request->customer);
                }
                $incomesData->groupBy('month', 'year');
                $incomesData = $incomesData->get();

                $incomeArr   = [];
                foreach($incomesData as $k => $incomeData)
                {
                    $incomeArr[$incomeData->month] = $incomeData->amount;
                }
                for($i = 1; $i <= 12; $i++)
                {
                    $incomeTotal[] = array_key_exists($i, $incomeArr) ? $incomeArr[$i] : 0;
                }

                //---------------------------INVOICE INCOME-----------------------------------------------

                $invoices = Invoice:: selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,invoice_id,id')->where('workspace', getActiveWorkSpace())->where('status', '!=', 0)->where('invoice_module','!=','taskly');
                $invoices->whereRAW('YEAR(send_date) =?', [$year]);
                if(!empty($request->customer))
                {
                    $invoices->where('user_id', '=', $cust->user_id);
                }

                if(!empty($request->category))
                {
                    $invoices->where('category_id', '=', $request->category);
                }

                $invoices        = $invoices->get();
                $invoiceTmpArray = [];
                foreach($invoices as $invoice)
                {
                    $invoiceTmpArray[$invoice->category_id][$invoice->month][] = $invoice->getTotal();
                }

                $invoiceArray = [];
                foreach($invoiceTmpArray as $cat_id => $record)
                {
                    $invoice             = [];
                    $invoice['category'] = [];
                    if(module_is_active('ProductService'))
                    {
                        $invoice['category'] = !empty(\Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->where('type','1')->first()) ? \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->where('type','1')->first()->id : '';
                    }
                    $invoice['data']     = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $invoice['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                    }
                    $invoiceArray[] = $invoice;
                }

                $invoiceTotalArray = [];
                foreach($invoices as $invoice)
                {
                    $invoiceTotalArray[$invoice->month][] = $invoice->getTotal();
                }
                for($i = 1; $i <= 12; $i++)
                {
                    $invoiceTotal[] = array_key_exists($i, $invoiceTotalArray) ? array_sum($invoiceTotalArray[$i]) : 0;
                }

                $chartIncomeArr = array_map(
                    function (){
                        return array_sum(func_get_args());
                    }, $incomeTotal, $invoiceTotal
                );
                $data['chartIncomeArr'] = $chartIncomeArr;
                $data['incomeArr']      = $array;
                $data['invoiceArray']   = $invoiceArray;
                $data['account']        = $account;
                $data['customer']       = $customer;
                $data['category']       = $category;

                $filter['startDateRange'] = 'Jan-' . $year;
                $filter['endDateRange']   = 'Dec-' . $year;
                return view('account::report.income_summary', compact('filter'), $data);
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }

    }
    public function expenseSummary(Request $request)
    {

            if(Auth::user()->isAbleTo('report expense manage'))
            {
                $account = BankAccount::where('workspace', '=', getActiveWorkSpace())->get()->pluck('holder_name', 'id');

                $vendor = Vender::where('workspace', '=', getActiveWorkSpace())->get()->pluck('name', 'id');

                $category = [];
                if(module_is_active('ProductService'))
                {
                    $category = \Modules\ProductService\Entities\Category::where('created_by', '=', creatorId())->where('workspace_id', '=', getActiveWorkSpace())->where('type', '=', 2)->get()->pluck('name', 'id');
                }

                $data['monthList']  = $month = $this->yearMonth();
                $data['yearList']   = $this->yearList();
                $filter['category'] = __('All');
                $filter['vendor']   = __('All');

                if(isset($request->year))
                {
                    $year = $request->year;
                }
                else
                {
                    $year = date('Y');
                }
                $data['currentYear'] = $year;

                //   -----------------------------------------PAYMENT EXPENSE ------------------------------------------------------------
                $expenses = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id')->leftjoin('categories', 'payments.category_id', '=', 'categories.id')->where('categories.type', '=', 2);
                $expenses->where('payments.workspace', '=', getActiveWorkSpace());
                $expenses->whereRAW('YEAR(date) =?', [$year]);

                if(!empty($request->category))
                {
                    $expenses->where('category_id', '=', $request->category);
                    $cat = [];
                    if(module_is_active('ProductService'))
                    {
                        $cat                = \Modules\ProductService\Entities\Category::find($request->category);
                    }
                    $filter['category'] = !empty($cat) ? $cat->name : '';
                }
                if(!empty($request->vendor))
                {
                    $expenses->where('vendor_id', '=', $request->vendor);

                    $vend             = Vender::find($request->vendor);
                    $filter['vendor'] = !empty($vend) ? $vend->name : '';
                }

                $expenses->groupBy('month', 'year', 'category_id');
                $expenses = $expenses->get();
                $tmpArray = [];
                foreach($expenses as $expense)
                {
                    $tmpArray[$expense->category_id][$expense->month] = $expense->amount;
                }
                $array = [];
                foreach($tmpArray as $cat_id => $record)
                {
                    $tmp             = [];
                    $tmp['category'] = [];
                    if(module_is_active('ProductService'))
                    {
                        $tmp['category'] = !empty(\Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()) ? \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                    }
                    $tmp['data']     = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $tmp['data'][$i] = array_key_exists($i, $record) ? $record[$i] : 0;
                    }
                    $array[] = $tmp;
                }
                $expensesData = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year');
                $expensesData->where('payments.workspace', '=', getActiveWorkSpace());
                $expensesData->whereRAW('YEAR(date) =?', [$year]);

                if(!empty($request->category))
                {
                    $expensesData->where('category_id', '=', $request->category);
                }
                if(!empty($request->vendor))
                {
                    $expensesData->where('vendor_id', '=', $request->vendor);
                }
                $expensesData->groupBy('month', 'year');
                $expensesData = $expensesData->get();

                $expenseArr = [];
                foreach($expensesData as $k => $expenseData)
                {
                    $expenseArr[$expenseData->month] = $expenseData->amount;
                }
                for($i = 1; $i <= 12; $i++)
                {
                    $expenseTotal[] = array_key_exists($i, $expenseArr) ? $expenseArr[$i] : 0;
                }

                //     ------------------------------------BILL EXPENSE----------------------------------------------------

                $bills = Bill:: selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,bill_id,id')->where('workspace',getActiveWorkSpace())->where('status', '!=', 0);
                $bills->whereRAW('YEAR(send_date) =?', [$year]);

                if(!empty($request->vendor))
                {
                    $bills->where('vendor_id', '=', $request->vendor);
                }

                if(!empty($request->category))
                {
                    $bills->where('category_id', '=', $request->category);
                }
                $bills        = $bills->get();
                $billTmpArray = [];
                foreach($bills as $bill)
                {
                    $billTmpArray[$bill->category_id][$bill->month][] = $bill->getTotal();
                }

                $billArray = [];
                foreach($billTmpArray as $cat_id => $record)
                {

                    $bill             = [];
                    $bill['category'] = [];
                    if(module_is_active('ProductService'))
                    {
                        $bill['category'] = !empty(\Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()) ? \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                    }
                    $bill['data']     = [];
                    for($i = 1; $i <= 12; $i++)
                    {

                        $bill['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                    }
                    $billArray[] = $bill;
                }
                $billTotalArray = [];
                foreach($bills as $bill)
                {
                    $billTotalArray[$bill->month][] = $bill->getTotal();
                }
                for($i = 1; $i <= 12; $i++)
                {
                    $billTotal[] = array_key_exists($i, $billTotalArray) ? array_sum($billTotalArray[$i]) : 0;
                }

                $chartExpenseArr = array_map(
                    function (){
                        return array_sum(func_get_args());
                    }, $expenseTotal, $billTotal
                );


                $data['chartExpenseArr'] = $chartExpenseArr;
                $data['expenseArr']      = $array;
                $data['billArray']       = $billArray;
                $data['account']         = $account;
                $data['vendor']          = $vendor;
                $data['category']        = $category;

                $filter['startDateRange'] = 'Jan-' . $year;
                $filter['endDateRange']   = 'Dec-' . $year;

                return view('account::report.expense_summary', compact('filter'), $data);
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }

    }
    public function incomeVsExpenseSummary(Request $request)
    {

            if(Auth::user()->isAbleTo('report income vs expense manage'))
            {
                $account = BankAccount::where('workspace', '=', getActiveWorkSpace())->get()->pluck('holder_name', 'id');
                $vendor = Vender::where('workspace', '=', getActiveWorkSpace())->get()->pluck('name', 'id');
                $customer = Customer::where('workspace', '=', getActiveWorkSpace())->get()->pluck('name', 'id');
                $category = [];
                if(module_is_active('ProductService'))
                {
                    $category =  \Modules\ProductService\Entities\Category::where('workspace_id', '=',getActiveWorkSpace())->whereIn('type', [1,2])->get()->pluck('name', 'id');
                }
                $data['monthList'] = $month = $this->yearMonth();
                $data['yearList']  = $this->yearList();

                $filter['category'] = __('All');
                $filter['customer'] = __('All');
                $filter['vendor']   = __('All');

                if(isset($request->year))
                {
                    $year = $request->year;
                }
                else
                {
                    $year = date('Y');
                }
                $data['currentYear'] = $year;

                // ------------------------------TOTAL PAYMENT EXPENSE-----------------------------------------------------------
                $expensesData = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year');
                $expensesData->where('payments.workspace', '=', getActiveWorkSpace());
                $expensesData->whereRAW('YEAR(date) =?', [$year]);

                if(!empty($request->category))
                {
                    $expensesData->where('category_id', '=', $request->category);
                    $cat = [];
                    if(module_is_active('ProductService'))
                    {
                        $cat                =  \Modules\ProductService\Entities\Category::find($request->category);
                    }
                    $filter['category'] = !empty($cat) ? $cat->name : '';

                }
                if(!empty($request->vendor))
                {
                    $expensesData->where('vendor_id', '=', $request->vendor);

                    $vend             = Vender::find($request->vendor);
                    $filter['vendor'] = !empty($vend) ? $vend->name : '';
                }
                $expensesData->groupBy('month', 'year');
                $expensesData = $expensesData->get();

                $expenseArr = [];
                foreach($expensesData as $k => $expenseData)
                {
                    $expenseArr[$expenseData->month] = $expenseData->amount;
                }

                // ------------------------------TOTAL BILL EXPENSE-----------------------------------------------------------

                $bills = Bill:: selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,bill_id,id')->where('workspace', getActiveWorkSpace())->where('status', '!=', 0);
                $bills->whereRAW('YEAR(send_date) =?', [$year]);

                if(!empty($request->vendor))
                {
                    $bills->where('vendor_id', '=', $request->vendor);

                }

                if(!empty($request->category))
                {
                    $bills->where('category_id', '=', $request->category);
                }

                $bills        = $bills->get();
                $billTmpArray = [];
                foreach($bills as $bill)
                {
                    $billTmpArray[$bill->category_id][$bill->month][] = $bill->getTotal();
                }
                $billArray = [];
                foreach($billTmpArray as $cat_id => $record)
                {
                    $bill             = [];
                    $bill['category'] = [];
                    if(module_is_active('ProductService'))
                    {
                        $bill['category'] = !empty( \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()) ?  \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                    }
                    $bill['data']     = [];
                    for($i = 1; $i <= 12; $i++)
                    {

                        $bill['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                    }
                    $billArray[] = $bill;
                }

                $billTotalArray = [];
                foreach($bills as $bill)
                {
                    $billTotalArray[$bill->month][] = $bill->getTotal();
                }

                // ------------------------------TOTAL REVENUE INCOME-----------------------------------------------------------

                $incomesData = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year');
                $incomesData->where('revenues.workspace', '=', getActiveWorkSpace());
                $incomesData->whereRAW('YEAR(date) =?', [$year]);

                if(!empty($request->category))
                {
                    $incomesData->where('category_id', '=', $request->category);
                }
                if(!empty($request->customer))
                {
                    $incomesData->where('customer_id', '=', $request->customer);
                    $cust               = Customer::find($request->customer);
                    $filter['customer'] = !empty($cust) ? $cust->name : '';
                }
                $incomesData->groupBy('month', 'year');
                $incomesData = $incomesData->get();
                $incomeArr   = [];
                foreach($incomesData as $k => $incomeData)
                {
                    $incomeArr[$incomeData->month] = $incomeData->amount;
                }

                // ------------------------------TOTAL INVOICE INCOME-----------------------------------------------------------
                $invoices = Invoice:: selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,invoice_id,id')->where('workspace', getActiveWorkSpace())->where('status', '!=', 0)->where('invoice_module','!=','taskly');
                $invoices->whereRAW('YEAR(send_date) =?', [$year]);
                if(!empty($request->customer))
                {
                    $invoices->where('user_id', '=', $cust->user_id);
                }
                if(!empty($request->category))
                {
                    $invoices->where('category_id', '=', $request->category);
                }
                $invoices        = $invoices->get();
                $invoiceTmpArray = [];
                foreach($invoices as $invoice)
                {
                    $invoiceTmpArray[$invoice->category_id][$invoice->month][] = $invoice->getTotal();
                }

                $invoiceArray = [];
                foreach($invoiceTmpArray as $cat_id => $record)
                {

                    $invoice             = [];
                    $invoice['category'] = [];
                    if(module_is_active('ProductService'))
                    {
                        $invoice['category'] = !empty( \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()) ?  \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                    }
                    $invoice['data']     = [];
                    for($i = 1; $i <= 12; $i++)
                    {

                        $invoice['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                    }
                    $invoiceArray[] = $invoice;
                }

                $invoiceTotalArray = [];
                foreach($invoices as $invoice)
                {
                    $invoiceTotalArray[$invoice->month][] = $invoice->getTotal();
                }
                //        ----------------------------------------------------------------------------------------------------

                for($i = 1; $i <= 12; $i++)
                {
                    $paymentExpenseTotal[] = array_key_exists($i, $expenseArr) ? $expenseArr[$i] : 0;
                    $billExpenseTotal[]    = array_key_exists($i, $billTotalArray) ? array_sum($billTotalArray[$i]) : 0;

                    $RevenueIncomeTotal[] = array_key_exists($i, $incomeArr) ? $incomeArr[$i] : 0;
                    $invoiceIncomeTotal[] = array_key_exists($i, $invoiceTotalArray) ? array_sum($invoiceTotalArray[$i]) : 0;

                }

                $totalIncome = array_map(
                    function (){
                        return array_sum(func_get_args());
                    }, $RevenueIncomeTotal, $invoiceIncomeTotal
                );

                $totalExpense = array_map(
                    function (){
                        return array_sum(func_get_args());
                    }, $paymentExpenseTotal, $billExpenseTotal
                );

                $profit = [];
                $keys   = array_keys($totalIncome + $totalExpense);
                foreach($keys as $v)
                {
                    $profit[$v] = (empty($totalIncome[$v]) ? 0 : $totalIncome[$v]) - (empty($totalExpense[$v]) ? 0 : $totalExpense[$v]);
                }


                $data['paymentExpenseTotal'] = $paymentExpenseTotal;
                $data['billExpenseTotal']    = $billExpenseTotal;
                $data['revenueIncomeTotal']  = $RevenueIncomeTotal;
                $data['invoiceIncomeTotal']  = $invoiceIncomeTotal;
                $data['profit']              = $profit;
                $data['account']             = $account;
                $data['vendor']              = $vendor;
                $data['customer']            = $customer;
                $data['category']            = $category;

                $filter['startDateRange'] = 'Jan-' . $year;
                $filter['endDateRange']   = 'Dec-' . $year;
                return view('account::report.income_vs_expense_summary', compact('filter'), $data);
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }

    }
    public function taxSummary(Request $request)
    {
        if(module_is_active('ProductService'))
        {
            if(Auth::user()->isAbleTo('report tax manage'))
            {
                $data['monthList'] = $month = $this->yearMonth();
                $data['yearList']  = $this->yearList();
                $data['taxList']   = $taxList = \Modules\ProductService\Entities\Tax::where('workspace_id', getActiveWorkSpace())->get();

                if(isset($request->year))
                {
                    $year = $request->year;
                }
                else
                {
                    $year = date('Y');
                }

                $data['currentYear'] = $year;

                $invoiceProducts = InvoiceProduct::selectRaw('invoice_products.* ,MONTH(invoice_products.created_at) as month,YEAR(invoice_products.created_at) as year')->leftjoin('product_services', 'invoice_products.product_id', '=', 'product_services.id')->whereRaw('YEAR(invoice_products.created_at) =?', [$year])->where('product_services.workspace_id', '=', getActiveWorkSpace())->get();
                $incomeTaxesData = [];
                foreach($invoiceProducts as $invoiceProduct)
                {
                    $incomeTax   = [];
                    $incomeTaxes = AccountUtility::tax($invoiceProduct->tax);

                    foreach($incomeTaxes as $taxe)
                    {
                        $taxDataPrice           = AccountUtility::taxRate(!empty($taxe) ? ($taxe->rate) : 0 , $invoiceProduct->price, $invoiceProduct->quantity);
                        $incomeTax[!empty($taxe) ? ($taxe->name):''] = $taxDataPrice;
                    }
                    $incomeTaxesData[$invoiceProduct->month][] = $incomeTax;
                }

                $income = [];
                foreach($incomeTaxesData as $month => $incomeTaxx)
                {
                    $incomeTaxRecord = [];
                    foreach($incomeTaxx as $k => $record)
                    {
                        foreach($record as $incomeTaxName => $incomeTaxAmount)
                        {
                            if(array_key_exists($incomeTaxName, $incomeTaxRecord))
                            {
                                $incomeTaxRecord[$incomeTaxName] += $incomeTaxAmount;
                            }
                            else
                            {
                                $incomeTaxRecord[$incomeTaxName] = $incomeTaxAmount;
                            }
                        }
                        $income['data'][$month] = $incomeTaxRecord;
                    }

                }

                foreach($income as $incomeMonth => $incomeTaxData)
                {
                    $incomeData = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $incomeData[$i] = array_key_exists($i, $incomeTaxData) ? $incomeTaxData[$i] : 0;
                    }

                }

                $incomes = [];
                if(isset($incomeData) && !empty($incomeData))
                {
                    foreach($taxList as $taxArr)
                    {
                        foreach($incomeData as $month => $tax)
                        {
                            if($tax != 0)
                            {
                                if(isset($tax[$taxArr->name]))
                                {
                                    $incomes[$taxArr->name][$month] = $tax[$taxArr->name];
                                }
                                else
                                {
                                    $incomes[$taxArr->name][$month] = 0;
                                }
                            }
                            else
                            {
                                $incomes[$taxArr->name][$month] = 0;
                            }
                        }
                    }
                }
                $billProducts = BillProduct::selectRaw('bill_products.* ,MONTH(bill_products.created_at) as month,YEAR(bill_products.created_at) as year')->leftjoin('product_services', 'bill_products.product_id', '=', 'product_services.id')->whereRaw('YEAR(bill_products.created_at) =?', [$year])->where('product_services.workspace_id', '=', getActiveWorkSpace())->get();

                $expenseTaxesData = [];
                foreach($billProducts as $billProduct)
                {
                    $billTax   = [];
                    $billTaxes = AccountUtility::tax($billProduct->tax);
                    foreach($billTaxes as $taxe)
                    {
                            $taxDataPrice         = AccountUtility::taxRate($taxe->rate, $billProduct->price, $billProduct->quantity);
                        $billTax[$taxe->name] = $taxDataPrice;
                    }
                    $expenseTaxesData[$billProduct->month][] = $billTax;
                }

                $bill = [];
                foreach($expenseTaxesData as $month => $billTaxx)
                {
                    $billTaxRecord = [];
                    foreach($billTaxx as $k => $record)
                    {
                        foreach($record as $billTaxName => $billTaxAmount)
                        {
                            if(array_key_exists($billTaxName, $billTaxRecord))
                            {
                                $billTaxRecord[$billTaxName] += $billTaxAmount;
                            }
                            else
                            {
                                $billTaxRecord[$billTaxName] = $billTaxAmount;
                            }
                        }
                        $bill['data'][$month] = $billTaxRecord;
                    }

                }

                foreach($bill as $billMonth => $billTaxData)
                {
                    $billData = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $billData[$i] = array_key_exists($i, $billTaxData) ? $billTaxData[$i] : 0;
                    }

                }
                $expenses = [];
                if(isset($billData) && !empty($billData))
                {

                    foreach($taxList as $taxArr)
                    {
                        foreach($billData as $month => $tax)
                        {
                            if($tax != 0)
                            {
                                if(isset($tax[$taxArr->name]))
                                {
                                    $expenses[$taxArr->name][$month] = $tax[$taxArr->name];
                                }
                                else
                                {
                                    $expenses[$taxArr->name][$month] = 0;
                                }
                            }
                            else
                            {
                                $expenses[$taxArr->name][$month] = 0;
                            }
                        }

                    }
                }

                $data['expenses'] = $expenses;
                $data['incomes']  = $incomes;

                $filter['startDateRange'] = 'Jan-' . $year;
                $filter['endDateRange']   = 'Dec-' . $year;

                return view('account::report.tax_summary', compact('filter'), $data);
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->route('home')->with('error', __('Please Enable Product & Service Module'));
        }

    }
    public function profitLossSummary(Request $request)
    {

            if(Auth::user()->isAbleTo('report loss & profit  manage'))
            {
                $data['month']     = [
                    'Jan-Mar',
                    'Apr-Jun',
                    'Jul-Sep',
                    'Oct-Dec',
                    'Total',
                ];
                $data['monthList'] = $month = $this->yearMonth();
                $data['yearList']  = $this->yearList();
                if(isset($request->year))
                {
                    $year = $request->year;
                }
                else
                {
                    $year = date('Y');
                }
                $data['currentYear'] = $year;

                // -------------------------------REVENUE INCOME-------------------------------------------------
                $incomes = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id');
                $incomes->where('workspace', '=', getActiveWorkSpace());
                $incomes->whereRAW('YEAR(date) =?', [$year]);
                $incomes->groupBy('month', 'year', 'category_id');
                $incomes        = $incomes->get();
                $tmpIncomeArray = [];
                foreach($incomes as $income)
                {
                    $tmpIncomeArray[$income->category_id][$income->month] = $income->amount;
                }

                $incomeCatAmount_1  = $incomeCatAmount_2 = $incomeCatAmount_3 = $incomeCatAmount_4 = 0;
                $revenueIncomeArray = array();
                foreach($tmpIncomeArray as $cat_id => $record)
                {

                    $tmp             = [];
                    $tmp['category'] = [];
                    if(module_is_active('ProductService'))
                    {
                        $tmp['category'] = !empty( \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()) ? \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                    }
                    $sumData         = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $sumData[] = array_key_exists($i, $record) ? $record[$i] : 0;
                    }

                    $month_1 = array_slice($sumData, 0, 3);
                    $month_2 = array_slice($sumData, 3, 3);
                    $month_3 = array_slice($sumData, 6, 3);
                    $month_4 = array_slice($sumData, 9, 3);


                    $incomeData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                    $incomeData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                    $incomeData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                    $incomeData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                    $incomeData[__('Total')]   = array_sum(
                        array(
                            $sum_1,
                            $sum_2,
                            $sum_3,
                            $sum_4,
                        )
                    );

                    $incomeCatAmount_1 += $sum_1;
                    $incomeCatAmount_2 += $sum_2;
                    $incomeCatAmount_3 += $sum_3;
                    $incomeCatAmount_4 += $sum_4;

                    $data['month'] = array_keys($incomeData);
                    $tmp['amount'] = array_values($incomeData);

                    $revenueIncomeArray[] = $tmp;

                }

                $data['incomeCatAmount'] = $incomeCatAmount = [
                    $incomeCatAmount_1,
                    $incomeCatAmount_2,
                    $incomeCatAmount_3,
                    $incomeCatAmount_4,
                    array_sum(
                        array(
                            $incomeCatAmount_1,
                            $incomeCatAmount_2,
                            $incomeCatAmount_3,
                            $incomeCatAmount_4,
                        )
                    ),
                ];

                $data['revenueIncomeArray'] = $revenueIncomeArray;

                //-----------------------INVOICE INCOME---------------------------------------------

                $invoices = Invoice:: selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,invoice_id,id')->where('workspace', getActiveWorkSpace())->where('status', '!=', 0)->where('invoice_module','!=','taskly');
                $invoices->whereRAW('YEAR(send_date) =?', [$year]);
                if(!empty($request->customer))
                {
                    $invoices->where('customer_id', '=', $request->customer);
                }
                $invoices        = $invoices->get();

                $invoiceTmpArray = [];
                foreach($invoices as $invoice)
                {
                    $invoiceTmpArray[$invoice->category_id][$invoice->month][] = $invoice->getTotal();
                }

                $invoiceCatAmount_1 = $invoiceCatAmount_2 = $invoiceCatAmount_3 = $invoiceCatAmount_4 = 0;
                $invoiceIncomeArray = array();
                foreach($invoiceTmpArray as $cat_id => $record)
                {

                    $invoiceTmp             = [];
                    $invoiceTmp['category'] = [];
                    if(module_is_active('ProductService'))
                    {
                        $invoiceTmp['category'] = !empty(\Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()) ? \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                    }
                    $invoiceSumData         = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $invoiceSumData[] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;

                    }

                    $month_1                          = array_slice($invoiceSumData, 0, 3);
                    $month_2                          = array_slice($invoiceSumData, 3, 3);
                    $month_3                          = array_slice($invoiceSumData, 6, 3);
                    $month_4                          = array_slice($invoiceSumData, 9, 3);
                    $invoiceIncomeData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                    $invoiceIncomeData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                    $invoiceIncomeData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                    $invoiceIncomeData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                    $invoiceIncomeData[__('Total')]   = array_sum(
                        array(
                            $sum_1,
                            $sum_2,
                            $sum_3,
                            $sum_4,
                        )
                    );
                    $invoiceCatAmount_1               += $sum_1;
                    $invoiceCatAmount_2               += $sum_2;
                    $invoiceCatAmount_3               += $sum_3;
                    $invoiceCatAmount_4               += $sum_4;

                    $invoiceTmp['amount'] = array_values($invoiceIncomeData);

                    $invoiceIncomeArray[] = $invoiceTmp;

                }

                $data['invoiceIncomeCatAmount'] = $invoiceIncomeCatAmount = [
                    $invoiceCatAmount_1,
                    $invoiceCatAmount_2,
                    $invoiceCatAmount_3,
                    $invoiceCatAmount_4,
                    array_sum(
                        array(
                            $invoiceCatAmount_1,
                            $invoiceCatAmount_2,
                            $invoiceCatAmount_3,
                            $invoiceCatAmount_4,
                        )
                    ),
                ];


                $data['invoiceIncomeArray'] = $invoiceIncomeArray;

                $data['totalIncome'] = $totalIncome = array_map(
                    function (){
                        return array_sum(func_get_args());
                    }, $invoiceIncomeCatAmount, $incomeCatAmount
                );

                //---------------------------------PAYMENT EXPENSE-----------------------------------

                $expenses = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id');
                $expenses->where('workspace', '=', getActiveWorkSpace());
                $expenses->whereRAW('YEAR(date) =?', [$year]);
                $expenses->groupBy('month', 'year', 'category_id');
                $expenses = $expenses->get();

                $tmpExpenseArray = [];
                foreach($expenses as $expense)
                {
                    $tmpExpenseArray[$expense->category_id][$expense->month] = $expense->amount;
                }

                $expenseArray       = [];
                $expenseCatAmount_1 = $expenseCatAmount_2 = $expenseCatAmount_3 = $expenseCatAmount_4 = 0;
                foreach($tmpExpenseArray as $cat_id => $record)
                {
                    $tmp             = [];
                    $tmp['category'] = [];
                    if(module_is_active('ProductService'))
                    {
                        $tmp['category'] = !empty(\Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()) ? \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                    }
                    $expenseSumData  = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $expenseSumData[] = array_key_exists($i, $record) ? $record[$i] : 0;

                    }

                    $month_1 = array_slice($expenseSumData, 0, 3);
                    $month_2 = array_slice($expenseSumData, 3, 3);
                    $month_3 = array_slice($expenseSumData, 6, 3);
                    $month_4 = array_slice($expenseSumData, 9, 3);

                    $expenseData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                    $expenseData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                    $expenseData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                    $expenseData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                    $expenseData[__('Total')]   = array_sum(
                        array(
                            $sum_1,
                            $sum_2,
                            $sum_3,
                            $sum_4,
                        )
                    );

                    $expenseCatAmount_1 += $sum_1;
                    $expenseCatAmount_2 += $sum_2;
                    $expenseCatAmount_3 += $sum_3;
                    $expenseCatAmount_4 += $sum_4;

                    $data['month'] = array_keys($expenseData);
                    $tmp['amount'] = array_values($expenseData);

                    $expenseArray[] = $tmp;

                }

                $data['expenseCatAmount'] = $expenseCatAmount = [
                    $expenseCatAmount_1,
                    $expenseCatAmount_2,
                    $expenseCatAmount_3,
                    $expenseCatAmount_4,
                    array_sum(
                        array(
                            $expenseCatAmount_1,
                            $expenseCatAmount_2,
                            $expenseCatAmount_3,
                            $expenseCatAmount_4,
                        )
                    ),
                ];
                $data['expenseArray']     = $expenseArray;

                //    ----------------------------EXPENSE BILL-----------------------------------------------------------------------

                $bills = Bill:: selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,bill_id,id')->where('workspace', getActiveWorkSpace())->where('status', '!=', 0);
                $bills->whereRAW('YEAR(send_date) =?', [$year]);
                if(!empty($request->customer))
                {
                    $bills->where('vendor_id', '=', $request->vendor);
                }
                $bills        = $bills->get();
                $billTmpArray = [];
                foreach($bills as $bill)
                {
                    $billTmpArray[$bill->category_id][$bill->month][] = $bill->getTotal();
                }

                $billExpenseArray       = [];
                $billExpenseCatAmount_1 = $billExpenseCatAmount_2 = $billExpenseCatAmount_3 = $billExpenseCatAmount_4 = 0;
                foreach($billTmpArray as $cat_id => $record)
                {
                    $billTmp             = [];
                    $billTmp['category'] = [];
                    if(module_is_active('ProductService'))
                    {
                        $billTmp['category'] = !empty(\Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()) ? \Modules\ProductService\Entities\Category::where('id', '=', $cat_id)->first()->name : '';
                    }
                    $billExpensSumData   = [];
                    for($i = 1; $i <= 12; $i++)
                    {
                        $billExpensSumData[] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                    }

                    $month_1 = array_slice($billExpensSumData, 0, 3);
                    $month_2 = array_slice($billExpensSumData, 3, 3);
                    $month_3 = array_slice($billExpensSumData, 6, 3);
                    $month_4 = array_slice($billExpensSumData, 9, 3);

                    $billExpenseData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                    $billExpenseData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                    $billExpenseData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                    $billExpenseData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                    $billExpenseData[__('Total')]   = array_sum(
                        array(
                            $sum_1,
                            $sum_2,
                            $sum_3,
                            $sum_4,
                        )
                    );

                    $billExpenseCatAmount_1 += $sum_1;
                    $billExpenseCatAmount_2 += $sum_2;
                    $billExpenseCatAmount_3 += $sum_3;
                    $billExpenseCatAmount_4 += $sum_4;

                    $data['month']     = array_keys($billExpenseData);
                    $billTmp['amount'] = array_values($billExpenseData);

                    $billExpenseArray[] = $billTmp;

                }

                $data['billExpenseCatAmount'] = $billExpenseCatAmount = [
                    $billExpenseCatAmount_1,
                    $billExpenseCatAmount_2,
                    $billExpenseCatAmount_3,
                    $billExpenseCatAmount_4,
                    array_sum(
                        array(
                            $billExpenseCatAmount_1,
                            $billExpenseCatAmount_2,
                            $billExpenseCatAmount_3,
                            $billExpenseCatAmount_4,
                        )
                    ),
                ];

                $data['billExpenseArray'] = $billExpenseArray;
                $data['totalExpense'] = $totalExpense = array_map(
                    function (){
                        return array_sum(func_get_args());
                    }, $billExpenseCatAmount, $expenseCatAmount
                );


                foreach($totalIncome as $k => $income)
                {
                    $netProfit[] = $income - $totalExpense[$k];
                }
                $data['netProfitArray'] = $netProfit;

                $filter['startDateRange'] = 'Jan-' . $year;
                $filter['endDateRange']   = 'Dec-' . $year;

                return view('account::report.profit_loss_summary', compact('filter'), $data);
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }


    }
    public function invoiceSummary(Request $request)
    {
        if(Auth::user()->isAbleTo('report invoice manage'))
        {
            $filter['customer'] = __('All');
            $filter['status']   = __('All');

            $customer = Customer::where('workspace', '=',getActiveWorkSpace())->get()->pluck('name', 'id');

            $status = Invoice::$statues;

            $invoices = Invoice::selectRaw('invoices.*,MONTH(send_date) as month,YEAR(send_date) as year');

            if($request->status != '')
            {
                $invoices->where('status', $request->status);

                $filter['status'] = Invoice::$statues[$request->status];
            }
            else
            {
                $invoices->where('status', '!=', 0);
            }

            $invoices->where('workspace', '=', getActiveWorkSpace());

            if(!empty($request->start_month) && !empty($request->end_month))
            {
                $start = strtotime($request->start_month);
                $end   = strtotime($request->end_month);
            }
            else
            {
                $start = strtotime(date('Y-01'));
                $end   = strtotime(date('Y-12'));
            }

            $invoices->where('send_date', '>=', date('Y-m-01', $start))->where('send_date', '<=', date('Y-m-t', $end));


            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange']   = date('M-Y', $end);

            if(!empty($request->customer))
            {
                $cust = Customer::find($request->customer);
                $invoices->where('user_id', $cust->user_id);

                $filter['customer'] = !empty($cust) ? $cust->name : '';
            }
            $invoices = $invoices->get();

            $totalInvoice      = 0;
            $totalDueInvoice   = 0;
            $invoiceTotalArray = [];
            foreach($invoices as $invoice)
            {
                $totalInvoice    += $invoice->getTotal();
                $totalDueInvoice += $invoice->getDue();

                $invoiceTotalArray[$invoice->month][] = $invoice->getTotal();
            }
            $totalPaidInvoice = $totalInvoice - $totalDueInvoice;

            for($i = 1; $i <= 12; $i++)
            {
                $invoiceTotal[] = array_key_exists($i, $invoiceTotalArray) ? array_sum($invoiceTotalArray[$i]) : 0;
            }

            $monthList = $month = $this->yearMonth();
            return view('account::report.invoice_report', compact('invoices', 'customer', 'status', 'totalInvoice', 'totalDueInvoice', 'totalPaidInvoice', 'invoiceTotal', 'monthList', 'filter'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function billSummary(Request $request)
    {
        if(Auth::user()->isAbleTo('report bill manage'))
        {
            $filter['vendor'] = __('All');
            $filter['status'] = __('All');

            $vendor = Vender::where('workspace', '=',getActiveWorkSpace())->get()->pluck('name', 'id');

            $status = Bill::$statues;

            $bills = Bill::selectRaw('bills.*,MONTH(send_date) as month,YEAR(send_date) as year')->select('bills.*', 'vendors.name as vendor_name','categories.name as categories_name','bill_payments.date as lastpayment_date')->join('vendors', 'bills.vendor_id', '=', 'vendors.id')->join('categories','bills.category_id','=','categories.id')->join('bill_payments','bills.bill_id','=','bill_payments.bill_id')->where('bill_module','!=','taskly');
            
            if(!empty($request->start_month) && !empty($request->end_month))
            {
                $start = strtotime($request->start_month);
                $end   = strtotime($request->end_month);
            }
            else
            {
                $start = strtotime(date('Y-01'));
                $end   = strtotime(date('Y-12'));
            }

            $bills->where('bills.send_date', '>=', date('Y-m-01', $start))->where('bills.send_date', '<=', date('Y-m-t', $end));

            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange']   = date('M-Y', $end);


            if(!empty($request->vendor))
            {
                $bills->where('bills.vendor_id', $request->vendor);
                $vend = Vender::find($request->vendor);

                $filter['vendor'] = !empty($vend) ? $vend->name : '';
            }

            if($request->status != '')
            {
                $bills->where('bills.status', '=', $request->status);

                $filter['status'] = Bill::$statues[$request->status];
            }
            else
            {
                $bills->where('status', '!=', 0);
            }

            $bills->where('bills.workspace', '=', getActiveWorkSpace());
            $bills = $bills->get();
            $totalBill      = 0;
            $totalDueBill   = 0;
            $billTotalArray = [];
            foreach($bills as $bill)
            {
                $totalBill    += $bill->getTotal();
                $totalDueBill += $bill->getDue();

                $billTotalArray[$bill->month][] = $bill->getTotal();
            }
            $totalPaidBill = $totalBill - $totalDueBill;

            for($i = 1; $i <= 12; $i++)
            {
                $billTotal[] = array_key_exists($i, $billTotalArray) ? array_sum($billTotalArray[$i]) : 0;
            }

            $monthList = $month = $this->yearMonth();

            return view('account::report.bill_report', compact('bills', 'vendor', 'status', 'totalBill', 'totalDueBill', 'totalPaidBill', 'billTotal', 'monthList', 'filter'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    public function productStock(Request $request)
    {
        if(Auth::user()->isAbleTo('report stock manage'))
        {
             $stocks = StockReport::where('workspace', '=', getActiveWorkSpace())->leftjoin('product_services', 'stock_reports.product_id', '=', 'product_services.id')->select('stock_reports.*', 'product_services.name as name')->get();
             return view('account::report.product_stock_report',compact('stocks'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    public function yearMonth()
    {

        $month[] = __('January');
        $month[] = __('February');
        $month[] = __('March');
        $month[] = __('April');
        $month[] = __('May');
        $month[] = __('June');
        $month[] = __('July');
        $month[] = __('August');
        $month[] = __('September');
        $month[] = __('October');
        $month[] = __('November');
        $month[] = __('December');

        return $month;
    }

    public function yearList()
    {
        $starting_year = date('Y', strtotime('-5 year'));
        $ending_year   = date('Y');

        foreach(range($ending_year, $starting_year) as $year)
        {
            $years[$year] = $year;
        }

        return $years;
    }
    
    //ZOHO INTEGRATION
    
  
    

    private function getZohoCredentials()
    {
        $workspaceId = getActiveWorkSpace();
        
        // Configuration array for Zoho credentials
        $zohoConfig = [
            'workspaces' => [
                4 => [
                'client_id' => '1000.SKMCX0NRDO3393CFOZDSYUBCYFJU0L',
                'client_secret' => '82cd9ea7924aaba0aed4b08fa3e48b84b160111e46',
                'organization_id' => '825350594',
                'redirect_uri' => 'https://erp.gtextholdings.com/zoho',
            ],
            6 => [
                'client_id' => '1000.UJPVKCASI3JAC61YCDENI6SSAXYJWJ',
                'client_secret' => '082b0792524e292b8667f5b00886b926b2e36251cc',
                'organization_id' => '886683167',
                'redirect_uri' => 'https://erp.gtextholdings.com/zoho',
            ],
            7 => [
                'client_id' => '1000.ZDPF8GCONR3XICTRF25GCXVWELO0RV',
                'client_secret' => 'bf478de1b4e2ca34a96c64da308602b6ba883d1bbf',
                'organization_id' => '886681992',
                'redirect_uri' => 'https://erp.gtextholdings.com/zoho',
            ],
            9 => [
                'client_id' => '1000.BJ2CKQBOVWY9TVG4J2QHHUT6OQVYJD',
                'client_secret' => '06322bc12de2da23c054248710a17b907e650abf8f',
                'organization_id' => '886776421',
                'redirect_uri' => 'https://erp.gtextholdings.com/zoho',
            ],
            ],
        ];
        
        $credentials = $zohoConfig['workspaces'][$workspaceId] ?? null;
        
        if (!$credentials) {
            throw new \Exception('Zoho credentials not found for workspace ID: ' . $workspaceId);
        }
        
        return $credentials;
    }
    
    
    
    
    public function connect()
{
    try {
        $zohoConfig = $this->getZohoCredentials();
        return view('account::zoho.connect', compact('zohoConfig'));
    } catch (\Exception $e) {
        return redirect()->back()->with('error', $e->getMessage());
    }
}
    
    
    
    /**
     * Refresh Zoho access token
     * @param string $organizationId
     * @return string
     * @throws \Exception
     */
    private function refreshZohoToken($organizationId)
    {
        $zohoConfig = $this->getZohoCredentials();
        
        $response = Http::asForm()->post('https://accounts.zoho.com/oauth/v2/token', [
            'refresh_token' => Session::get('zoho_refresh_token_' . $zohoConfig['organization_id']),
            'client_id' => $zohoConfig['client_id'],
            'client_secret' => $zohoConfig['client_secret'],
            'grant_type' => 'refresh_token',
        ]);
        
        $tokenData = $response->json();
        
        if (isset($tokenData['access_token'])) {
            Session::put('zoho_access_token_' . $zohoConfig['organization_id'], $tokenData['access_token']);
            return $tokenData['access_token'];
        }
        
        throw new \Exception('Failed to refresh token');
    }


    /**
     * Redirect to Zoho OAuth authorization URL
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToZoho()
    {
        
        try {
            $zohoConfig = $this->getZohoCredentials();
            
            $params = [
                'scope' => 'ZohoBooks.fullaccess.all',
                'client_id' => $zohoConfig['client_id'],
                'state' => 'testing',
                'response_type' => 'code',
                'redirect_uri' => $zohoConfig['redirect_uri'],
                'access_type' => 'online',
            ];
        
            $authUrl = 'https://accounts.zoho.com/oauth/v2/auth?' . http_build_query($params);
            return redirect($authUrl);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Handle Zoho OAuth callback
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function handleZohoCallback(Request $request)
    {
        try {
            $code = $request->query('code');
            $zohoConfig = $this->getZohoCredentials();
        
            $response = Http::asForm()->post('https://accounts.zoho.com/oauth/v2/token', [
                'code' => $code,
                'client_id' => $zohoConfig['client_id'],
                'client_secret' => $zohoConfig['client_secret'],
                'redirect_uri' => $zohoConfig['redirect_uri'],
                'grant_type' => 'authorization_code',
            ]);
        
            $tokenData = $response->json();
        
            if (isset($tokenData['access_token'])) {
                Session::put('zoho_access_token_' . $zohoConfig['organization_id'], $tokenData['access_token']);
                if (isset($tokenData['refresh_token'])) {
                    Session::put('zoho_refresh_token_' . $zohoConfig['organization_id'], $tokenData['refresh_token']);
                }
                return "Connected Successfully!";
            }
        
            return response()->json(['error' => 'Failed to get token', 'details' => $tokenData], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    
    

    /**
     * Logout from Zoho, clearing session data
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function zohoLogout()
    {
       
        try {
            $zohoConfig = $this->getZohoCredentials();
            $organizationId = $zohoConfig['organization_id'];

            // Prepare debug data for console logging
            $debugData = [
                'workspace_id' => getActiveWorkSpace(),
                'organization_id' => $organizationId,
                'session_keys_before' => array_keys(Session::all()),
            ];

            // Clear Zoho tokens for this organization
            $accessTokenKey = 'zoho_access_token_' . $organizationId;
            $refreshTokenKey = 'zoho_refresh_token_' . $organizationId;

            $tokensExist = Session::has($accessTokenKey) || Session::has($refreshTokenKey);
            if ($tokensExist) {
                Session::forget($accessTokenKey);
                Session::forget($refreshTokenKey);
                $debugData['tokens_cleared'] = true;
            } else {
                $debugData['tokens_cleared'] = false;
                $debugData['note'] = 'No Zoho tokens found for organization ID: ' . $organizationId;
            }

            $debugData['session_keys_after'] = array_keys(Session::all());

            // Embed JavaScript for console logging
            $jsDebug = '<script>console.log(' . json_encode($debugData, JSON_PRETTY_PRINT) . ');</script>';

            // Return redirect with success message and debug script
            return redirect()->route('zoho.connect')
                ->with('success', __('Disconnected from Zoho successfully.'))
                ->with('debug', $jsDebug);
        } catch (\Exception $e) {
            $errorData = [
                'error' => 'Zoho logout failed',
                'message' => $e->getMessage(),
                'workspace_id' => getActiveWorkSpace(),
                'organization_id' => isset($zohoConfig) ? $zohoConfig['organization_id'] : 'unknown',
            ];
            $jsError = '<script>console.error(' . json_encode($errorData, JSON_PRETTY_PRINT) . ');</script>';
            return redirect()->back()
                ->with('error', __('Failed to disconnect from Zoho: ') . $e->getMessage())
                ->with('debug', $jsError);
        }
    }



    /**
     * Get Zoho invoices
     * @return \Illuminate\Http\JsonResponse|Renderable
     */
    public function getInvoices()
    {
        if (!Auth::user()->isAbleTo('user manage')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        
        try {
            $zohoConfig = $this->getZohoCredentials();
            $token = Session::get('zoho_access_token_' . $zohoConfig['organization_id']);
        
            if (!$token) {
                return redirect()->route('zoho.redirect')->with('error', 'Token missing. Please connect to Zoho first.');
            }
        
            $response = Http::withHeaders([
                'Authorization' => 'Zoho-oauthtoken ' . $token,
            ])->get("https://www.zohoapis.com/books/v3/invoices", [
                'organization_id' => $zohoConfig['organization_id']
            ]);
        
            if ($response->successful()) {
                $invoices = $response->json()['invoices'];
                return view('account::invoice.invoice_zoho', compact('invoices'));
            } elseif ($response->status() == 401) {
                // Attempt to refresh token
                $token = $this->refreshZohoToken($zohoConfig['organization_id']);
                $response = Http::withHeaders([
                    'Authorization' => 'Zoho-oauthtoken ' . $token,
                ])->get("https://www.zohoapis.com/books/v3/invoices", [
                    'organization_id' => $zohoConfig['organization_id']
                ]);
                
                if ($response->successful()) {
                    $invoices = $response->json()['invoices'];
                    return view('account::invoice.invoice_zoho', compact('invoices'));
                }
            }
            
            return response()->json([
                'error' => 'Failed to fetch invoices',
                'response' => $response->json()
            ], $response->status());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    
    
    /**
 * Get Zoho products (items)
 * @return \Illuminate\Http\JsonResponse|Renderable
 */
public function getProducts()
{
    try {
        $zohoConfig = $this->getZohoCredentials();
        $token = Session::get('zoho_access_token_' . $zohoConfig['organization_id']);
    
        if (!$token) {
            return redirect()->route('zoho.redirect')->with('error', 'Token missing. Please connect to Zoho first.');
        }
    
        $response = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $token,
        ])->get("https://www.zohoapis.com/books/v3/items", [
            'organization_id' => $zohoConfig['organization_id']
        ]);
    
        // Console log for debugging
        $debugData = [
            'action' => 'Fetching Zoho products',
            'organization_id' => $zohoConfig['organization_id'],
            'response_status' => $response->status(),
        ];
    
        if ($response->successful()) {
            $products = $response->json()['items'];
            $debugData['products_count'] = count($products);
            $jsDebug = '<script>console.log(' . json_encode($debugData, JSON_PRETTY_PRINT) . ');</script>';
            return view('account::product.product_zoho', compact('products'))
                ->with('debug', $jsDebug);
        } elseif ($response->status() == 401) {
            // Attempt to refresh token
            $token = $this->refreshZohoToken($zohoConfig['organization_id']);
            $response = Http::withHeaders([
                'Authorization' => 'Zoho-oauthtoken ' . $token,
            ])->get("https://www.zohoapis.com/books/v3/items", [
                'organization_id' => $zohoConfig['organization_id']
            ]);
            
            if ($response->successful()) {
                $products = $response->json()['items'];
                $debugData['products_count'] = count($products);
                $debugData['token_refreshed'] = true;
                $jsDebug = '<script>console.log(' . json_encode($debugData, JSON_PRETTY_PRINT) . ');</script>';
                return view('account::product.product_zoho', compact('products'))
                    ->with('debug', $jsDebug);
            }
        }
        
        $debugData['error'] = 'Failed to fetch products';
        $debugData['response'] = $response->json() ?? [];
        $jsError = '<script>console.error(' . json_encode($debugData, JSON_PRETTY_PRINT) . ');</script>';
        return response()->json([
            'error' => 'Failed to fetch products',
            'response' => $response->json()
        ], $response->status())->with('debug', $jsError);
    } catch (\Exception $e) {
        $errorData = [
            'error' => 'Zoho products fetch failed',
            'message' => $e->getMessage(),
            'organization_id' => isset($zohoConfig) ? $zohoConfig['organization_id'] : 'unknown',
        ];
        $jsError = '<script>console.error(' . json_encode($errorData, JSON_PRETTY_PRINT) . ');</script>';
        return redirect()->back()
            ->with('error', $e->getMessage())
            ->with('debug', $jsError);
    }
}

    /**
     * Get Zoho journals
     * @return \Illuminate\Http\JsonResponse|Renderable
     */
    public function getJournals()
    {
        if (!Auth::user()->isAbleTo('user manage')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        
        try {
            $zohoConfig = $this->getZohoCredentials();
            $token = Session::get('zoho_access_token_' . $zohoConfig['organization_id']);
        
            if (!$token) {
                return redirect()->route('zoho.redirect')->with('error', 'Token missing. Please connect to Zoho first.');
            }
        
            $response = Http::withHeaders([
                'Authorization' => 'Zoho-oauthtoken ' . $token,
            ])->get("https://www.zohoapis.com/books/v3/journals", [
                'organization_id' => $zohoConfig['organization_id']
            ]);
        
            if ($response->successful()) {
                $journals = $response->json()['journals'];
                return view('account::jornal.journal_section', compact('journals'));
            } elseif ($response->status() == 401) {
                $token = $this->refreshZohoToken($zohoConfig['organization_id']);
                $response = Http::withHeaders([
                    'Authorization' => 'Zoho-oauthtoken ' . $token,
                ])->get("https://www.zohoapis.com/books/v3/journals", [
                    'organization_id' => $zohoConfig['organization_id']
                ]);
                
                if ($response->successful()) {
                    $journals = $response->json()['journals'];
                    return view('account::jornal.journal_section', compact('journals'));
                }
            }
            
            return response()->json([
                'error' => 'Failed to fetch journals',
                'response' => $response->json()
            ], $response->status());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get Zoho chart of accounts
     * @return \Illuminate\Http\JsonResponse|Renderable
     */
    public function getChartOfAccounts()
    {
        if (!Auth::user()->isAbleTo('user manage')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        
        try {
            $zohoConfig = $this->getZohoCredentials();
            $token = Session::get('zoho_access_token_' . $zohoConfig['organization_id']);
        
            if (!$token) {
                return redirect()->route('zoho.redirect')->with('error', 'Token missing. Please connect to Zoho first.');
            }
        
            $response = Http::withHeaders([
                'Authorization' => 'Zoho-oauthtoken ' . $token,
            ])->get("https://www.zohoapis.com/books/v3/chartofaccounts", [
                'organization_id' => $zohoConfig['organization_id']
            ]);
        
            if ($response->successful()) {
                $accounts = $response->json()['chartofaccounts'];
                return view('account::chartOfAccount.account_section', compact('accounts'));
            } elseif ($response->status() == 401) {
                $token = $this->refreshZohoToken($zohoConfig['organization_id']);
                $response = Http::withHeaders([
                    'Authorization' => 'Zoho-oauthtoken ' . $token,
                ])->get("https://www.zohoapis.com/books/v3/chartofaccounts", [
                    'organization_id' => $zohoConfig['organization_id']
                ]);
                
                if ($response->successful()) {
                    $accounts = $response->json()['chartofaccounts'];
                    return view('account::chartOfAccount.account_section', compact('accounts'));
                }
            }
            
            return response()->json([
                'error' => 'Failed to fetch chart of accounts',
                'response' => $response->json()
            ], $response->status());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get Zoho expenses
     * @return \Illuminate\Http\JsonResponse|Renderable
     */
    public function getExpenses()
    {
        if (!Auth::user()->isAbleTo('user manage')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        
        try {
            $zohoConfig = $this->getZohoCredentials();
            $token = Session::get('zoho_access_token_' . $zohoConfig['organization_id']);
        
            if (!$token) {
                return redirect()->route('zoho.redirect')->with('error', 'Token missing. Please connect to Zoho first.');
            }
        
            $response = Http::withHeaders([
                'Authorization' => 'Zoho-oauthtoken ' . $token,
            ])->get("https://www.zohoapis.com/books/v3/expenses", [
                'organization_id' => $zohoConfig['organization_id']
            ]);
        
            if ($response->successful()) {
                $expenses = $response->json()['expenses'];
                return view('account::expenses.expense_section', compact('expenses'));
            } elseif ($response->status() == 401) {
                $token = $this->refreshZohoToken($zohoConfig['organization_id']);
                $response = Http::withHeaders([
                    'Authorization' => 'Zoho-oauthtoken ' . $token,
                ])->get("https://www.zohoapis.com/books/v3/expenses", [
                    'organization_id' => $zohoConfig['organization_id']
                ]);
                
                if ($response->successful()) {
                    $expenses = $response->json()['expenses'];
                    return view('account::expenses.expense_section', compact('expenses'));
                }
            }
            
            return response()->json([
                'error' => 'Failed to fetch expenses',
                'response' => $response->json()
            ], $response->status());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get Zoho opening balances
     * @return \Illuminate\Http\JsonResponse|Renderable
     */
    public function getOpeningBalances()
    {
        if (!Auth::user()->isAbleTo('user manage')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        
        try {
            $zohoConfig = $this->getZohoCredentials();
            $token = Session::get('zoho_access_token_' . $zohoConfig['organization_id']);
        
            if (!$token) {
                return redirect()->route('zoho.redirect')->with('error', 'Token missing. Please connect to Zoho first.');
            }
        
            $response = Http::withHeaders([
                'Authorization' => 'Zoho-oauthtoken ' . $token,
            ])->get("https://www.zohoapis.com/books/v3/settings/openingbalances", [
                'organization_id' => $zohoConfig['organization_id']
            ]);
        
            if ($response->successful()) {
                $openingBalances = $response->json()['opening_balances'] ?? $response->json()['opening_balance'] ?? [];
                return view('account::openingbalances.opening_balance_section', compact('openingBalances'));
            } elseif ($response->status() == 401) {
                $token = $this->refreshZohoToken($zohoConfig['organization_id']);
                $response = Http::withHeaders([
                    'Authorization' => 'Zoho-oauthtoken ' . $token,
                ])->get("https://www.zohoapis.com/books/v3/settings/openingbalances", [
                    'organization_id' => $zohoConfig['organization_id']
                ]);
                
                if ($response->successful()) {
                    $openingBalances = $response->json()['opening_balances'] ?? $response->json()['opening_balance'] ?? [];
                    return view('account::openingbalances.opening_balance_section', compact('openingBalances'));
                }
            }
            
            return response()->json([
                'error' => 'Failed to fetch opening balances',
                'response' => $response->json()
            ], $response->status());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get Zoho contacts
     * @return \Illuminate\Http\JsonResponse|Renderable
     */
    public function getContacts()
    {
        if (!Auth::user()->isAbleTo('user manage')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        
        try {
            $zohoConfig = $this->getZohoCredentials();
            $token = Session::get('zoho_access_token_' . $zohoConfig['organization_id']);
        
            if (!$token) {
                return redirect()->route('zoho.redirect')->with('error', 'Token missing. Please connect to Zoho first.');
            }
        
            $response = Http::withHeaders([
                'Authorization' => 'Zoho-oauthtoken ' . $token,
            ])->get("https://www.zohoapis.com/books/v3/contacts", [
                'organization_id' => $zohoConfig['organization_id']
            ]);
        
            if ($response->successful()) {
                $contacts = $response->json()['contacts'];
                return view('account::contacts.index', compact('contacts'));
            } elseif ($response->status() == 401) {
                $token = $this->refreshZohoToken($zohoConfig['organization_id']);
                $response = Http::withHeaders([
                    'Authorization' => 'Zoho-oauthtoken ' . $token,
                ])->get("https://www.zohoapis.com/books/v3/contacts", [
                    'organization_id' => $zohoConfig['organization_id']
                ]);
                
                if ($response->successful()) {
                    $contacts = $response->json()['contacts'];
                    return view('account::contacts.index', compact('contacts'));
                }
            }
            
            return response()->json([
                'error' => 'Failed to fetch contacts',
                'response' => $response->json()
            ], $response->status());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get Zoho customer payments
     * @return \Illuminate\Http\JsonResponse|Renderable
     */
    public function getCustomerPayments()
    {
        if (!Auth::user()->isAbleTo('user manage')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        
        try {
            $zohoConfig = $this->getZohoCredentials();
            $token = Session::get('zoho_access_token_' . $zohoConfig['organization_id']);
        
            if (!$token) {
                return redirect()->route('zoho.redirect')->with('error', 'Token missing. Please connect to Zoho first.');
            }
        
            $response = Http::withHeaders([
                'Authorization' => 'Zoho-oauthtoken ' . $token,
            ])->get("https://www.zohoapis.com/books/v3/customerpayments", [
                'organization_id' => $zohoConfig['organization_id']
            ]);
        
            if ($response->successful()) {
                $payments = $response->json()['customerpayments'];
                return view('account::zoho-payment.index', compact('payments'));
            } elseif ($response->status() == 401) {
                $token = $this->refreshZohoToken($zohoConfig['organization_id']);
                $response = Http::withHeaders([
                    'Authorization' => 'Zoho-oauthtoken ' . $token,
                ])->get("https://www.zohoapis.com/books/v3/customerpayments", [
                    'organization_id' => $zohoConfig['organization_id']
                ]);
                
                if ($response->successful()) {
                    $payments = $response->json()['customerpayments'];
                    return view('account::zoho-payment.index', compact('payments'));
                }
            }
            
            return response()->json([
                'error' => 'Failed to fetch customer payments',
                'response' => $response->json()
            ], $response->status());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    

//   public function redirectToZoho()
//   {
//         $params = [
//             'scope' => 'ZohoBooks.fullaccess.all',
//             'client_id' => '1000.SKMCX0NRDO3393CFOZDSYUBCYFJU0L',
//             'state' => 'testing',
//             'response_type' => 'code',
//             'redirect_uri' => 'https://erp.gtextholdings.com/zoho',
//             'access_type' => 'online',
//         ];
    
//         $authUrl = 'https://accounts.zoho.com/oauth/v2/auth?' . http_build_query($params);
//         return redirect($authUrl);
//     }

    
//      public function handleZohoCallback(Request $request)
//     {
//         $code = $request->query('code');
    
//         $response = Http::asForm()->post('https://accounts.zoho.com/oauth/v2/token', [
//             'code' => $code,
//             'client_id' => '1000.SKMCX0NRDO3393CFOZDSYUBCYFJU0L',
//             'client_secret' => '82cd9ea7924aaba0aed4b08fa3e48b84b160111e46',
//             'redirect_uri' => 'https://erp.gtextholdings.com/zoho',
//             'grant_type' => 'authorization_code',
//         ]);
    
//         $tokenData = $response->json();
    
//         if (isset($tokenData['access_token'])) {
//             Session::put('zoho_access_token', $tokenData['access_token']);
//             //return $tokenData['access_token'];
            
//             return "Connected Successfully!";
            
          
//         }
    
//         return response()->json(['error' => 'Failed to get token', 'details' => $tokenData], 400);
//     }
    
    
//     public function getInvoices()
//     {
//         $token = Session::get('zoho_access_token');
//         $organizationId = '825350594';
    
//         if (!$token) {
//             return redirect()->route('zoho.redirect')->with('error', 'Token missing. Please connect to Zoho first.');
//         }
    
//         $response = Http::withHeaders([
//             'Authorization' => 'Zoho-oauthtoken ' . $token,
//         ])->get("https://www.zohoapis.com/books/v3/invoices", [
//             'organization_id' => $organizationId
//         ]);
    
//         if ($response->successful()) {
//             $invoice = $response->json()['invoices'];
//             return view('account::invoice.invoice_section', compact('invoice'));
//         } else {
//             return response()->json([
//                 'error' => 'Failed to fetch invoices',
//                 'response' => $response->json()
//             ]);
//         }
//     }

    
//     public function getJournals()
//     {
//         $organizationId = '825350594';
//         $token = Session::get('zoho_access_token');
    
//         $response = Http::withToken($token)->get("https://www.zohoapis.com/books/v3/journals", [
//             'organization_id' => $organizationId
//         ]);
    
//         if ($response->successful()) {
//             $journals = $response->json()['journals'];
    
//             return view('account::jornal.journal_section', compact('journals'));

//         } else {
//             return $response->body(); // Return the error for debugging
//         }
//     }
    
//     public function getChartOfAccounts()
//     {
//         $organizationId = '825350594'; // Replace with your actual org ID
//         $token = Session::get('zoho_access_token');
    
//         $response = Http::withToken($token)->get("https://www.zohoapis.com/books/v3/chartofaccounts", [
//             'organization_id' => $organizationId
//         ]);
    
//         if ($response->successful()) {
//             $accounts = $response->json()['chartofaccounts'];
//             return view('account::chartOfAccount.account_section', compact('accounts'));

//         } else {
//             return $response->body(); // for debugging
//         }
//     }
    
//     public function getExpenses()
//     {
//         $organizationId = '825350594'; // Replace with your actual org ID
//         $token = Session::get('zoho_access_token');
    
//         $response = Http::withToken($token)->get("https://www.zohoapis.com/books/v3/expenses", [
//             'organization_id' => $organizationId
//         ]);
    
//         if ($response->successful()) {
//             $expenses = $response->json()['expenses']; // Or check full structure if needed
//             return view('account::expenses.expense_section', compact('expenses'));

//         } else {
//             return $response->body(); // Optional: For debugging or logging
//         }
//     }
    
    
//     public function getOpeningBalances()
//     {
//         $organizationId = '825350594'; 
//       $token = Session::get('zoho_access_token');
    
//         $response = Http::withToken($token)->get('https://www.zohoapis.com/books/v3/settings/openingbalances', [
//             'organization_id' => $organizationId
//         ]);
    
//         if ($response->successful()) {
//             $openingBalances = $response->json()['opening_balances'] ?? [];
//             $openingBalances = $response->json()['opening_balance'] ?? [];
//             return view('account::openingbalances.opening_balance_section', compact('openingBalances'));
//         } else {
//             return $response->body(); // Optional for debugging
//         }
//     }
    
    
//     public function getContacts()
//     {
//         $organizationId = '825350594'; 
//         $token = Session::get('zoho_access_token'); 
    
//         $response = Http::withHeaders([
//             'Authorization' => 'Zoho-oauthtoken ' . $token,
//         ])->get('https://www.zohoapis.com/books/v3/contacts', [
//             'organization_id' => $organizationId
//         ]);
    
//         if ($response->successful()) {
//             $contacts = $response->json()['contacts'];
//             return view('account::contacts.index', compact('contacts')); 
//             //return response()->json($contacts);
//         } else {
//             return response()->json([
//                 'error' => 'Failed to fetch contacts',
//                 'details' => $response->json(),
//             ], $response->status());
//         }
//     }
    
    
//     public function getCustomerPayments()
//     {
//         $organizationId = '825350594'; 
//         $token = Session::get('zoho_access_token');
    
//         $response = Http::withToken($token)->get('https://www.zohoapis.com/books/v3/customerpayments', [
//             'organization_id' => $organizationId
//         ]);
    
//         if ($response->successful()) {
//             $payments = $response->json()['customerpayments']; 
//             return view('account::zoho-payment.index', compact('payments'));
//         } else {
//             return response()->json([
//                 'error' => 'Failed to fetch customer payments',
//                 'details' => $response->json(),
//             ], $response->status());
//         }
//     }




}

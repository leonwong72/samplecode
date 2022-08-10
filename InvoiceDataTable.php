<?php

namespace App\DataTables\Staff;

use App\Models\Branch;
use App\Models\Guardian;
use App\Models\Invoice;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class InvoiceDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('checkbox', function($item){
                if($item->payments()->count() == 0 || ($item->payments()->count() > 0 &&  $item->payments()->latest()->first()->is_approved == 2)) {
                    return '<input type="checkbox" class="form-check-input" name="invoice_ids[]" value="'.$item->id.'">';
                }
            })
            ->addColumn('guardian_name', function($item){
                return $item->invoiceable->name;
            })
            ->addColumn('guardian_code', function($item){
                return $item->invoiceable->code;
            })
            ->addColumn('student_name', function($item){
                return $item->student->name;
            })
            ->addColumn('student_code', function($item){
                return $item->student->code;
            })
            ->addColumn('amount', function($item){
                if($item->is_paid) {
                    return '<span class="text-success">'.$item->amount.'</span>';
                } else {
                    return '<span class="text-danger">'.$item->amount.'</span>';
                }
            })
            ->addColumn('issue_date', function($item){
                return $item->created_at->format('d-m-Y');
            })
            ->addColumn('issue_by', function($item){
                if($item->generatable){
                    return $item->generatable->name.' ('.$item->generatable->code.')';
                }else{
                    return 'System Generated';
                }
            })
            ->addColumn('make_payment', function($item){
                if(!$item->is_paid){
                    return '
                        <a href="'.route('branch.invoices.show',$item->id).'">
                            <i class="fas fa-credit-card"></i> Make Payment
                        </a>
                    ';
                }else{
                    return '
                        <a href="'.route('branch.invoices.show',$item->id).'">
                            <i class="fas fa-eye"></i> View Detail
                        </a>
                    ';
                }
            })
            ->addColumn('action', function ($item) {
                if(!$item->is_paid){
                    return '
                        <a class="text-danger delFunc" data-id="'.$item->id.'" style="cursor: pointer">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    
                        <form method="POST" action="'.route("branch.invoices.destroy", $item->id).'" class="destroy_'.$item->id.'">'.csrf_field().method_field('DELETE').'</form>
                    ';
                }
            })
            ->rawColumns(['checkbox','amount','make_payment','action']);
    }

    public function query(Invoice $model)
    {
        return $model->where('invoiceable_type',Guardian::class)->localsearch()->latest('id');
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('branch-invoices-table')
            ->columns($this->getColumns())
            ->ajax([
                'url' => route('branch.invoices.index'),
                'data' => 'function(d) {
                    d.title = $("[name=\'title\']").val();
                    d.invoice_number = $("[name=\'invoice_number\']").val();
                    d.bill_to = $("[name=\'bill_to\']").val();
                }',
            ])
            ->dom("<'d-flex justify-content-end tw-py-2' p><'row'<'col-sm-12' t>><'row'<'col-lg-12' <'tw-py-3 col-lg-12 d-flex flex-column flex-sm-row align-items-center justify-content-between tw-space-y-5 md:tw-space-y-0' ip>r>>")
            ->initComplete('function() {
                    $(".datatable-input").on("change",function () {
                        $("#branch-invoices-table").DataTable().ajax.reload();
                    });
                    $("#subBtn").on("click",function () {
                        $("#branch-invoices-table").DataTable().ajax.reload();
                    });
                    $("#clearBtn").on("click",function () {
                        $("[name=\'title\']").val(null);
                        $("[name=\'invoice_number\']").val(null);
                        $("[name=\'bill_to\']").val(null);
                        $("[name=\'bill_to\']").change();
                        $("#branch-invoices-table").DataTable().ajax.reload();
                    });
                    $("#branch-invoices-table").on("click", ".delFunc", function(e) {
                        var del_id = ".destroy_" + $(this).attr("data-id");
                        event.preventDefault();
                        Swal.fire({
                            title: "Are you sure?",
                            text: "You won\"t be able to revert this!",
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonText: "Yes, Delete it!"
                        }).then(function(result) {
                            if (result.value) {
                                $(del_id).submit();
                            }
                            else{
                                if(id.prop("checked")) { id.prop("checked", false); }
                                else { id.prop("checked", true); }
                            }
                        });
                    });
                    $(document).ready(function(){
                        $(".payment-method").hide();
                        $(".select-method").on("change", function () {
                            if ($(this).val() == 2) {
                                $(".payment-method").show();
                            } else {
                                $(".payment-method").hide();
                            }
                        });
                    })
                    function update_checkbox_list(){
                        $("[name=\'invoice_ids[]\']").each(function(){
                            var check = $("[name=\'invoice_ids2[]\'][value=\'"+$(this).val()+"\']").prop("checked");
                            $(this).prop("checked", check);
                        });
                    }

                    function count_invoice_checked(){
                        if($("[name=\'invoice_ids2[]\']:checked").length > 0){
                            $(".bulk-make-payment").removeClass("d-none");
                        }else{
                            $(".bulk-make-payment").addClass("d-none");
                        }
                        
                        $(".btn-bulk-make-payment").text("Make Payment ("+$("[name=\'invoice_ids2[]\']:checked").length+")");
                    }

                    function check_all_checked(){
                        if($("[name=\'invoice_ids2[]\']:checked").length == $("[name=\'invoice_ids2[]\']").length){
                            $(".select-all").prop("checked", true)
                        }else{
                            $(".select-all").prop("checked", false)
                        }
                    }

                    $("[name=\'invoice_ids[]\']").click(function(){
                        var input = $("[name=\'invoice_ids2[]\'][value=\'"+$(this).val()+"\']");
                        if($(this).is(":checked")) {
                            input.prop("checked", true);
                        } else {
                            input.prop("checked", false);
                        }
                    
                        count_invoice_checked();
                        check_all_checked();
                    });

                    $(".select-all").click(function () {
                        if($(this).is(":checked")){
                            $("[name=\'invoice_ids2[]\'").prop("checked", true);
                        }else{
                            $("[name=\'invoice_ids2[]\'").prop("checked", false);
                        }

                        update_checkbox_list();
                        count_invoice_checked();
                    });

                    $("#branch-invoices-table_wrapper .paginate_button").click(function(){
                        setTimeout(function(){
                            update_checkbox_list();
                        }, 1000);
                    });
                }');
    }

    protected function getColumns()
    {
        return [
            Column::make('checkbox')->title('<input type="checkbox" class="form-check-input select-all">')->titleAttr('Check All Invoices')->orderable(false),
            Column::make('invoice_number')->title('Invoice Number'),
            Column::make('issue_by')->title('Issue By'),
            Column::make('issue_date')->title('Issue Date'),
            Column::make('guardian_name')->title('Guardian Name'),
            Column::make('guardian_code')->title('Guardian Code'),
            Column::make('student_name')->title('Student Name'),
            Column::make('student_code')->title('Student Code'),
            Column::make('title')->title('Billing Information'),
            Column::make('amount')->title('Amount'),
            Column::make('make_payment')->title('Payment/Receipt'),
            Column::make('action')->className('text-end')->title('')->sorting(false),
        ];
    }

    protected function filename()
    {
        return 'staff\Bills_' . date('YmdHis');
    }
}

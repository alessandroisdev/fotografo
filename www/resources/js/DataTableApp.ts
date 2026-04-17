import $ from 'jquery';
import 'datatables.net-bs5';

// Expõe globalmente se necessário
(window as any).$ = $;
(window as any).jQuery = $;

export interface ColumnDefinition {
    data: string;
    name?: string;
    title?: string;
    orderable?: boolean;
    searchable?: boolean;
    render?: (data: any, type: any, row: any) => string;
}

export interface DataTableOptions {
    selector: string;
    url: string;
    columns: ColumnDefinition[];
    rowCallback?: (row: Node, data: any[] | object, index: number) => void;
    drawCallback?: (settings: any) => void;
}

export class DataTableApp {
    private table: any;

    constructor(private options: DataTableOptions) {
        this.init();
    }

    private init() {
        this.table = ($(this.options.selector) as any).DataTable({
            processing: true,
            serverSide: true,
            ajax: this.options.url,
            columns: this.options.columns,
            rowCallback: this.options.rowCallback,
            drawCallback: this.options.drawCallback,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
            },
            responsive: true,
            pageLength: 25,
        });
    }

    public reload() {
        this.table.ajax.reload(null, false);
    }
    
    public getTable() {
        return this.table;
    }
}

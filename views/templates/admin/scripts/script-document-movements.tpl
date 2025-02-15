{literal}
    <script type="text/javascript">
        function createTableMovement(id_document, id_mvt_reason, movements) {
            console.log(`createTableMovement(${ id_document }, ${ id_mvt_reason }, movements: ${ movements.length })`);

            const button = $("<button></button>")
                .addClass("btn btn-info btn-sm btn-action float-right")
                .attr("data-action", "add")
                .attr("data-id_document", id_document)
                .attr("data-id_mvt_reason", id_mvt_reason)
                .attr("title", "Aggiungi movimento")
                .append(
                    $("<i></i>")
                    .addClass("material-icons")
                    .text("add_circle")
                )
                .append("<span>Aggiungi movimento</span>")

            const panel_heading = $("<div></div>")
                .addClass("panel-heading")
                .append($("<span></span>").addClass("panel-title"))
                .append(button);

            const panel_body = $("<div></div>")
                .addClass("panel-body")
                .append($("<div></div>").addClass("panel-body"));

            const table = $("<table></table>")
                .addClass("table table-striped table-bordered table-hover")
                .append(createTrHeader())
                .append(createTrBody(movements));

            $(panel_body).append(table);

            const panel = $("<div></div>")
                .addClass("panel")
                .attr("data-id_document", id_document)
                .append(
                    panel_heading
                )
                .append(panel_body)
                .append($("<div></div>")
                    .addClass("panel-body"));

            if (!movements.length) {
                $(panel).find(".panel-title").text("Nessun movimento.");
            } else {
                $(panel).find(".panel-title").text(
                    "Movimenti del documento " + movements[0].document_number + " (" + movements.length + ")"
                );
            }

            return panel;
        }

        function createTrBody(movements) {
            const tbody = $("<tbody></tbody>");

            movements.forEach(movement => {
                const tr = createTrMovement(movement);
                tbody.append(tr);
            });

            return tbody;
        }

        function createTrHeader() {
            const thead = $("<thead></thead>");

            const tr = $("<tr></tr>");

            const th_check = $("<th></th>").html("<input type='checkbox' class='check-all'>");
            const th_product = $("<th></th>").text("Prodotto");
            const th_combination = $("<th></th>").text("Combinazione");
            const th_reference = $("<th></th>").text("Riferimento");
            const th_ean13 = $("<th></th>").text("Ean13");
            const th_price = $("<th></th>").text("Prezzo");
            const th_quantity = $("<th></th>").text("Stock iniziale");
            const th_movement = $("<th></th>").text("Movimento");
            const th_quantity_after = $("<th></th>").text("Stock finale");
            const th_date = $("<th></th>").text("Data inserimento");
            const th_employee = $("<th></th>").text("Operatore");
            const th_actions = $("<th></th>").text("Azioni");

            tr.append(th_check);
            tr.append(th_product);
            tr.append(th_combination);
            tr.append(th_reference);
            tr.append(th_ean13);
            tr.append(th_price);
            tr.append(th_quantity);
            tr.append(th_movement);
            tr.append(th_quantity_after);
            tr.append(th_date);
            tr.append(th_employee);
            tr.append(th_actions);

            thead.append(tr);

            return thead;
        }

        function createTrMovement(movement) {
            const tr = $("<tr></tr>")
                .attr("data-id", movement.id_mpstock_movement)
                .attr("title", "Movimento id: " + movement.id_mpstock_movement);

            const td_check = $("<td></td>");
            const input = $("<input type='checkbox' name='id_mpstock_movement[]' value='" + movement.id_mpstock_movement + "'>");
            td_check.append(input);
            tr.append(td_check);

            const td_product = $("<td></td>").text(movement.product_name);
            tr.append(td_product);

            const td_combination = $("<td></td>").text(movement.product_combination);
            tr.append(td_combination);

            const td_reference = $("<td></td>").text(movement.reference);
            tr.append(td_reference);

            const td_ean13 = $("<td></td>").text(movement.ean13);
            tr.append(td_ean13);

            const td_price = $("<td></td>").text(movement.price_te);
            tr.append(td_price);

            const td_quantity = $("<td></td>").text(movement.stock_quantity_before);
            tr.append(td_quantity);

            const td_movement = $("<td></td>").text(movement.stock_movement);
            tr.append(td_movement);

            const td_quantity_after = $("<td></td>").text(movement.stock_quantity_after);
            tr.append(td_quantity_after);

            const td_date = $("<td></td>").text(movement.date_add);
            tr.append(td_date);

            const td_employee = $("<td></td>").text(movement.employee);
            tr.append(td_employee);

            const td_actions = $("<td></td>");
            const buttons = `
            <div class="d-flex justify-content-center">
<button data-id="${movement.id_mpstock_movement}" class='btn btn-sm btn-action' data-action="edit" title="Modifica"><i class='material-icons'>edit</i></button>
<button data-id="${movement.id_mpstock_movement}" class='btn btn-sm btn-action' data-action="delete" title="Elimina"><i class='material-icons'>delete</i></button>
            </div>
            `;
            td_actions.append(buttons);
            tr.append(td_actions);

            return tr;
        }
    </script>
{/literal}
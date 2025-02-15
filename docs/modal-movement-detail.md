# Documentazione Modale Dettaglio Movimento

## Elementi del Form

| ID Elemento | Name Attribute | Tipo | Descrizione |
|------------|----------------|------|-------------|
| `detail-id_product` | `id_product` | input hidden | ID prodotto associato |
| `detail-id_mpstock_document` | `id_mpstock_document` | input hidden | ID documento di stock |
| `detail-id_mpstock_movement` | `id_mpstock_movement` | input text | ID movimento (sola lettura) |
| `detail-id_mpstock_mvt_reason` | `id_mpstock_mvt_reason` | select | Tipologia movimento (con segno quantità) |
| `detail-ean13` | `ean13` | input text | Ricerca per codice EAN13 |
| `detail-reference` | `reference` | input text | Ricerca per riferimento prodotto |
| `detail-product_name` | `product_name` | input text | Ricerca per nome prodotto |
| `detail-id_product_attribute` | `id_product_attribute` | select | Selezione variante prodotto |
| `detail-quantity-actual` | `quantity_actual` | input number | Quantità attuale (sola lettura) |
| `detail-quantity` | `quantity` | input number | Quantità da movimentare |
| `detail-sign` | `sign` | input hidden | Segno operazione (impostato automaticamente) |
| `detail-quantity-total` | `quantity_total` | input number | Quantità totale (sola lettura) |
| `detail-id_supplier` | `id_supplier` | select | Selezione fornitore |
| `detail-id_employee` | `id_employee` | input text | ID dipendente (sola lettura) |

## Funzionalità Principali

1. **Visualizzazione/Modifica Dettagli Movimento**
   - Mostra tutti i dettagli di un movimento esistente
   - Permette la modifica dei campi editabili

2. **Selezione Prodotto**
   - Ricerca prodotto tramite EAN13, riferimento o nome
   - Autocomplete con suggerimenti live
   - Aggiornamento automatico delle varianti disponibili

3. **Gestione Quantità**
   - Visualizzazione quantità attuale (readonly)
   - Inserimento quantità da movimentare
   - Calcolo automatico quantità totale
   - Gestione segno operazione basata sul tipo movimento

4. **Selezione Fornitore**
   - Dropdown con lista completa fornitori
   - Validazione campo obbligatorio

5. **Salvataggio Modifiche**
   - Validazione client-side di tutti i campi
   - Invio dati via AJAX
   - Gestione feedback visivo

## Technical Details

- **Form ID**: `movement-detail-form`
- **Modal ID**: `modal-movement-detail`
- **Pulsante Salvataggio**: `submit-movement-detail`
- **Dependencies**:
  - jQuery
  - Bootstrap Modal
  - Chosen per select avanzati
  - Autocomplete custom (gestito in `movements.js`)

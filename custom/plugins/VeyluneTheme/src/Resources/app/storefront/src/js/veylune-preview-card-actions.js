import {
    emitSelectionChange,
    readSelectionState,
    upsertSelectionItem,
    writeSelectionState,
} from './veylune-preview-selection-store';

const cards = [...document.querySelectorAll('[data-plp-card]')];

const itemForCard = (card) => ({
    productId: card.dataset.previewRecord,
    productName: card.dataset.plpName,
    material: card.dataset.plpMaterialLabel,
    unitPrice: Number(card.dataset.plpPrice),
    quantity: 1,
});

const lineForCard = (state, card) => state.items.find((item) => (
    item.productId === card.dataset.previewRecord
    && item.material === card.dataset.plpMaterialLabel
));

const renderCardState = (card, state) => {
    const button = card.querySelector('[data-preview-card-add]');
    if (!button) return;

    const line = lineForCard(state, card);
    button.textContent = line ? 'Add another' : 'Add to cart';
    button.dataset.cartQuantity = String(line?.quantity || 0);
    card.classList.toggle('is-in-cart', Boolean(line));
};

const renderAllCards = () => {
    const state = readSelectionState();
    cards.forEach((card) => renderCardState(card, state));
};

cards.forEach((card) => {
    const button = card.querySelector('[data-preview-card-add]');
    const status = card.querySelector('[data-preview-card-status]');
    if (!button) return;

    button.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();

        const { state, item } = upsertSelectionItem(readSelectionState(), itemForCard(card));
        if (!item) return;

        const written = writeSelectionState(state);
        emitSelectionChange(written);
        renderAllCards();

        button.textContent = `Added - Qty ${item.quantity}`;
        if (status) status.textContent = `${card.dataset.plpName} added to cart. Quantity ${item.quantity}.`;
        window.setTimeout(() => renderCardState(card, readSelectionState()), 1400);
    });
});

window.addEventListener('veylune:selection-change', renderAllCards);
window.addEventListener('storage', renderAllCards);
renderAllCards();
/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const SANDBOX_MODAL_COMPONENT_NAME = 'sylius_paypal:create_sandbox_modal';

function getSandboxModalWrapper(modal) {
    const parent = modal.parentElement;

    if (
        parent === null ||
        parent === document.body ||
        parent.getAttribute('data-live-name-value') !== SANDBOX_MODAL_COMPONENT_NAME
    ) {
        return null;
    }

    return parent;
}

function portalSandboxModal(event) {
    const wrapper = getSandboxModalWrapper(event.target);

    if (wrapper === null) {
        return;
    }

    const placeholder = document.createComment(SANDBOX_MODAL_COMPONENT_NAME);
    wrapper.before(placeholder);
    document.body.appendChild(wrapper);

    event.target.addEventListener('hidden.bs.modal', () => placeholder.replaceWith(wrapper), { once: true });
    event.stopImmediatePropagation();
}

document.addEventListener('show.bs.modal', portalSandboxModal, { capture: true });

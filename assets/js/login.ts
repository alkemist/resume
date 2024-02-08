import {browserSupportsWebAuthn, browserSupportsWebAuthnAutofill, startAuthentication} from '@simplewebauthn/browser';
import {_fetch, BASE_OPTIONS_CONFIG, showError} from "./webauthn";

const login = async (email: string, callbackUrl: string) => {
    if (browserSupportsWebAuthn()) {
        const autofill = await browserSupportsWebAuthnAutofill();

        /*console.log("autofill ?", autofill);
        console.log("isConditionalMediationAvailable ?",
            PublicKeyCredential.isConditionalMediationAvailable !== undefined ?
                await PublicKeyCredential.isConditionalMediationAvailable() : false);
        console.log("isUserVerifyingPlatformAuthenticatorAvailable ?",
            PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable !== undefined ?
                await PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable() : false);*/

        const baseUrl = '/api/authenticate';
        const optionsConfig = {
            ...BASE_OPTIONS_CONFIG,
            username: email,
            mediation: "optional",
            useBrowserAutofill: true,
        }

        const optionResponse = await _fetch(`${baseUrl}/options`, optionsConfig);
        const options = await optionResponse.json();

        try {
            // useBrowserAutofill option don't work correctly on Chrome Android
            const credential = await startAuthentication(options, false);

            const loginResponse = await _fetch(baseUrl, credential);
            const login = await loginResponse.json();

            if (login.status === "ok") {
                if (callbackUrl) {
                    setTimeout(() => {
                        document.location.href = callbackUrl;
                    }, 100)
                    return "redirect...";
                }
                return "logged";
            } else {
                return showError({code: login.status, message: login.errorMessage})
            }
        } catch (e) {
            return showError(e);
        }
    }
}

document.addEventListener('DOMContentLoaded', async function () {
    const element = document.querySelector('.webauthn-data') as unknown as HTMLElement & { dataset: any };
    const form = document.querySelector('.webauthn-form') as HTMLFormElement;

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const email = formData.get(element.dataset.emailField)

            if (email) {
                element.innerHTML = await login(email.toString(), element.dataset.callbackUrl);
            }
        })
    }
});
import {browserSupportsWebAuthn, startRegistration} from "@simplewebauthn/browser";
import {_fetch, BASE_OPTIONS_CONFIG, showError, WebAuthnResponse} from "./webauthn";

const register = async (id?: string, email?: string, username?: string, callbackUrl?: string) => {
    if (browserSupportsWebAuthn()) {
        const baseUrl = `/api/register${id ? `/${id}` : ''}`;
        const optionsConfig = {
            ...BASE_OPTIONS_CONFIG,
            attestation: 'none',
            username: email ?? '',
            displayName: username ?? '',
            extensions: {
                credProps: true
            }
        }

        try {
            const optionResponse = await _fetch(`${baseUrl}/options`, optionsConfig);
            const options = await optionResponse.json();

            const credential = await startRegistration(options);

            const regiserResponse = await _fetch(baseUrl, credential);
            const register = await regiserResponse.json() as WebAuthnResponse;

            if (register.status === "ok") {
                if (callbackUrl) {
                    setTimeout(() => {
                        document.location.href = callbackUrl;
                    }, 3000);
                    return "redirect...";
                }
                return "registered";
            } else {
                return showError({code: register.status, message: register.errorMessage})
            }
        } catch (e) {
            return showError(e);
        }
    }
}

document.addEventListener('DOMContentLoaded', async function () {
    const element = document.querySelector('.webauthn-data') as unknown as HTMLElement & { dataset: any };
    const user = JSON.parse(element.dataset.user) as {
        id?: string,
        email?: string,
        username?: string
    };

    element.innerHTML = await register(user.id, user.email, user.username, element.dataset.callbackUrl);
});
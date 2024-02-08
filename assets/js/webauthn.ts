export const _fetch = (uri: string, body: any) => {
    return fetch(uri, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(body)
        }
    );
}

export const BASE_OPTIONS_CONFIG = {
    authenticatorType: 'roaming',
    excludeCredentials: [],
    //userVerification: "discouraged",
    authenticatorSelection: {
        requireResidentKey: false, //true
        authenticatorAttachment: 'cross-platform',
        //userVerification: "discouraged",
    }
};

export const showError = (e: Error | WebAuthnError): string => {
    if ((e as WebAuthnError).code) {
        const er = e as WebAuthnError;
        // ERROR_PASSTHROUGH_SEE_CAUSE_PROPERTY => annulé
        // ERROR_AUTHENTICATOR_GENERAL_ERROR => n'existe pas
        // ERROR_AUTHENTICATOR_PREVIOUSLY_REGISTERED => Déjà enregistré
        console.log("Known error", er.code, er.message)
        return `[${er.code}] : ${er.message}`;
    } else {
        // NotReadableError
        console.log("Unknown error", e)
        return e.toString();
    }
}

export interface WebAuthnResponse {
    status: string;
    errorMessage: string;
}

export interface WebAuthnError {
    code: string;
    message: string;
}
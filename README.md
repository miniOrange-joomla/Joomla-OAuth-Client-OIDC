# Joomla OAuth Client – OAuth 2.0 & OpenID Connect (OIDC) SSO

The **miniOrange Joomla OAuth Client** extension enables users to log in to Joomla websites using accounts from external OAuth 2.0 and OpenID Connect (OIDC) identity providers. It helps integrate Joomla with existing authentication systems and enables Single Sign-On (SSO).

- **Product Page:** https://plugins.miniorange.com/joomla-single-sign-on-sso-oauth-oidc
- **Repository:** https://github.com/miniOrange-joomla/Joomla-OAuth-Client-OIDC

## Features

- **OAuth 2.0 and OIDC SSO:** Enable secure login to Joomla using external identity providers.
- **Multiple Identity Providers:** Integrate with providers such as Google, Microsoft Entra ID, Keycloak, Okta, and AWS Cognito.
- **User Profile Mapping:** Map identity provider attributes to Joomla user profiles.
- **User Registration:** Enable automatic user registration where supported.
- **Group Mapping:** Map identity provider groups to Joomla user groups where supported.
- **Flexible Configuration:** Configure client credentials, redirect URLs, scopes, and provider endpoints.
- **PKCE Support:** Support PKCE authentication flows where available.

For the complete feature list and edition-specific capabilities, refer to the [official product page](https://plugins.miniorange.com/joomla-single-sign-on-sso-oauth-oidc).

## Installation

1. Download the latest available package from the [GitHub Releases](https://github.com/miniOrange-joomla/Joomla-OAuth-Client-OIDC/releases) page or the [official miniOrange product page](https://plugins.miniorange.com/joomla-single-sign-on-sso-oauth-oidc).
2. Log in to your Joomla Administrator panel.
3. Navigate to **System → Install → Extensions**, or open the extension installation screen available in your Joomla version.
4. Upload the downloaded ZIP package and complete the installation.
5. Open the miniOrange OAuth Client extension and proceed to configuration.

**Note:** Installation menu labels may differ between Joomla versions.

## Configuration

To configure OAuth or OIDC Single Sign-On:

1. Open the miniOrange OAuth Client extension in Joomla Administrator.
2. Select or configure your identity provider.
3. Register your Joomla site as a client application with the identity provider.
4. Copy the callback or redirect URL displayed by the extension and add it to the identity provider's application configuration.
5. Enter the required client credentials and configure the authorization, token, and user-information endpoints as required.
6. Configure scopes and user attribute mapping as needed.
7. Save the settings and test the login flow.

Refer to the [official setup documentation](https://plugins.miniorange.com/joomla-sso-ldap-mfa-solutions?section=oauth-client) for detailed instructions.

## Supported Identity Providers

The extension can integrate with OAuth 2.0 and OIDC-compatible identity providers, including:

- Google
- Microsoft Entra ID (Azure AD)
- Keycloak
- Okta
- AWS Cognito
- Salesforce
- Other compatible OAuth 2.0 and OIDC providers

Actual compatibility depends on the provider's supported protocols, configuration, and the features available in your plugin edition.

## Requirements

- A Joomla website running a version compatible with the extension.
- Access to the Joomla Administrator panel.
- An account and application registration with your chosen identity provider.
- The required client credentials, redirect URL, scopes, and endpoints.

## Releases

Download the latest available release from the [GitHub Releases](https://github.com/miniOrange-joomla/Joomla-OAuth-Client-OIDC/releases) page.

Review the release notes for updates, bug fixes, security fixes, and compatibility information before upgrading.

## Support

For installation assistance, configuration guidance, or product-related questions:

- **Product Documentation:** [Joomla OAuth Client – miniOrange](https://plugins.miniorange.com/joomla-single-sign-on-sso-oauth-oidc)
- **GitHub Issues:** [Report an Issue](https://github.com/miniOrange-joomla/Joomla-OAuth-Client-OIDC/issues)
- **Email:** joomlasupport@xecurify.com

## License

Refer to the [LICENSE](LICENSE) file in this repository for the applicable license terms.

---

Developed by **miniOrange** to help Joomla websites integrate with external OAuth 2.0 and OpenID Connect identity providers.

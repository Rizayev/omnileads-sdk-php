# Security Policy

## Reporting a Vulnerability

**Please do not open a public GitHub issue for security vulnerabilities.**

If you believe you have found a security issue in this SDK, email `security@example.com`
with:

- A description of the issue and its impact
- Steps to reproduce or proof-of-concept code
- Affected version(s) of the SDK
- Any suggested mitigation

You should receive an acknowledgement within five business days. Once the report is
validated we will work on a fix and coordinate disclosure with you.

## Supported Versions

Only the latest major release line receives security patches.

| Version | Supported          |
|---------|--------------------|
| 1.x     | :white_check_mark: |
| < 1.0   | :x:                |

## Hardening tips for users

- Never commit your `OMNILEADS_API_KEY` or `OMNILEADS_JWT` — load them from environment
  variables or a secret manager.
- Webhook endpoints should be protected with a shared secret in the URL or an IP allow
  list. The OmniLeads API spec does not currently sign webhook payloads, so transport
  protection is your responsibility.
- Always validate webhook payloads through `WebhookPayloadParser` rather than trusting
  the raw request body.

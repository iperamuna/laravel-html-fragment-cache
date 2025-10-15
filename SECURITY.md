# Security Policy

## Supported Versions

Use this section to tell people about which versions of your project are
currently being supported with security updates.

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

If you discover a security vulnerability within Laravel HTML Fragment Cache, please send an email to iperamuna@gmail.com. All security vulnerabilities will be promptly addressed.

Please do not disclose security vulnerabilities publicly until they have been handled by our team.

## Security Considerations

This package handles caching of HTML fragments and should be used with appropriate security considerations:

- **Cache Keys**: Ensure cache keys don't expose sensitive information
- **Cache Content**: Be careful not to cache sensitive data in HTML fragments
- **Cache TTL**: Set appropriate TTL values for your use case
- **Cache Store**: Use secure cache stores in production environments

## Best Practices

- Use environment-specific cache stores
- Regularly rotate cache keys when needed
- Monitor cache usage and performance
- Use appropriate TTL values for your content

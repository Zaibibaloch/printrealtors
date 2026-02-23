# GitHub Actions Deployment Setup

## Prerequisites

1. SSH key pair generate karein (agar nahi hai):
   ```bash
   ssh-keygen -t rsa -b 4096 -C "github-actions"
   ```

2. Public key server par add karein:
   ```bash
   # Local machine se:
   ssh-copy-id -p 65002 u907948652@85.31.227.160
   
   # Ya manually ~/.ssh/authorized_keys me add karein
   ```

## GitHub Secrets Setup

GitHub repository me jayein: Settings → Secrets and variables → Actions

Yeh secrets add karein:

1. **SSH_HOST**: `85.31.227.160`
2. **SSH_USERNAME**: `u907948652`
3. **SSH_PORT**: `65002`
4. **SSH_PRIVATE_KEY**: Private SSH key (jo generate ki thi)

## How to Get SSH Private Key

```bash
# Local machine par:
cat ~/.ssh/id_rsa
# Ya jo bhi key file hai uska content copy karein
```

## Deployment

- **Automatic**: Jab bhi `master` branch par push hoga, automatically deploy hoga
- **Manual**: Actions tab se manually bhi run kar sakte hain

## Notes

- Migration automatically run hogi (agar already run nahi hui)
- npm build step skip hoga agar npm available nahi hai
- Laravel cache automatically clear hoga

# Deployment

The plugin is deployed to the WordPress site over FTP using GitHub Actions.

## Workflows

Two workflows live in `.github/workflows/`:

| Workflow          | File               | Target                                             |
| ----------------- | ------------------ | -------------------------------------------------- |
| Deploy to PROD    | `deploy-prod.yml`  | `wp-content/plugins/trombongos_tour_wp/` (production) |
| Deploy to TEST    | `deploy-test.yml`  | Test environment                                   |

Both workflows are triggered manually (`workflow_dispatch`) and expose a
`dryRun` input (defaulting to `true`) so you can preview the file sync before
performing a real deployment.

## How it works

The workflows check out the latest code and sync the repository to the server
via FTP using the `webitsbr/github-to-ftp` action. Git metadata is excluded from
the upload.

### Required secrets

The FTP deployment relies on repository secrets:

- `SERVER` — FTP server host.
- `USER_PROD` / `PASSWORD_PROD` — credentials for the production deployment.
- The corresponding credentials for the test deployment.

## Running a deployment

1. Open the **Actions** tab on GitHub.
2. Choose the desired deploy workflow.
3. Run it with `dryRun` set to `true` first to verify the file list.
4. Re-run with `dryRun` set to `false` to perform the actual sync.

!!! warning
    The production sync uses a "clean slate" option, which removes files on the
    server that are not present in the repository (within the target directory).
    Always run a dry run first.

# Rate Limit Prevention Rules

- Work sequentially, never in parallel. Complete one file operation fully before starting the next.
- After reading or writing each file, pause and summarize what you did before continuing.
- Never read more than 3 files in a single response turn.
- Never write more than 2 files in a single response turn.
- If you need to modify many files, list them first and ask for confirmation before proceeding.
- Break large tasks into small steps and wait for user acknowledgment between steps.

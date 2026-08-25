import * as React from "react"

import { cn } from "@/lib/utils"

function Input({
  className,
  type,
  ...props
}) {
  return (
    <input
      type={type}
      data-slot="input"
      className={cn(
        "h-9 w-full min-w-0 rounded-md border border-edge bg-transparent px-3 py-1 text-base outline-none selection:bg-brand selection:text-brand-foreground file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-dim-foreground disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm",
        "focus-visible:border-brand focus-visible:ring-2 focus-visible:ring-brand/20",
        "aria-invalid:border-danger aria-invalid:ring-danger/20 dark:aria-invalid:ring-danger/40",
        className
      )}
      {...props} />
  );
}

export { Input }

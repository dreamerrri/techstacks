import * as React from "react"
import { cva } from "class-variance-authority";
import { Slot } from "radix-ui"

import { cn } from "@/lib/utils"

const badgeVariants = cva(
  "inline-flex w-fit shrink-0 items-center justify-center gap-1 overflow-hidden rounded-full border border-transparent px-2 py-0.5 text-xs font-medium whitespace-nowrap transition-[color,box-shadow] focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-danger aria-invalid:ring-danger/20 dark:aria-invalid:ring-danger/40 [&>svg]:pointer-events-none [&>svg]:size-3",
  {
    variants: {
      variant: {
        default: "bg-brand text-brand-foreground [a&]:hover:bg-brand/90",
        soft:
          "bg-soft text-soft-foreground [a&]:hover:bg-soft/90",
        danger:
          "bg-danger text-white focus-visible:ring-danger/20 dark:bg-danger/60 dark:focus-visible:ring-danger/40 [a&]:hover:bg-danger/90",
        outline:
          "border-border text-foreground [a&]:hover:bg-highlight [a&]:hover:text-highlight-foreground",
        ghost: "[a&]:hover:bg-highlight [a&]:hover:text-highlight-foreground",
        link: "text-brand underline-offset-4 [a&]:hover:underline",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  }
)

function Badge({
  className,
  variant = "default",
  asChild = false,
  ...props
}) {
  const Comp = asChild ? Slot.Root : "span"

  return (
    <Comp
      data-slot="badge"
      data-variant={variant}
      className={cn(badgeVariants({ variant }), className)}
      {...props} />
  );
}

export { Badge, badgeVariants }

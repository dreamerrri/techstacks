import {
  CircleCheckIcon,
  InfoIcon,
  Loader2Icon,
  OctagonXIcon,
  TriangleAlertIcon,
} from "lucide-react"
import { Toaster as Sonner } from "sonner"

// Maps the app's data-theme values to sonner's dark/light
const currentTheme = () =>
  document.documentElement.dataset.theme === "techstacks-light" ? "light" : "dark"

const Toaster = ({
  ...props
}) => {
  return (
    <Sonner
      theme={currentTheme()}
      className="toaster group"
      icons={{
        success: <CircleCheckIcon className="size-4" />,
        info: <InfoIcon className="size-4" />,
        warning: <TriangleAlertIcon className="size-4" />,
        error: <OctagonXIcon className="size-4" />,
        loading: <Loader2Icon className="size-4 animate-spin" />,
      }}
      style={
        {
          "--normal-bg": "var(--sc-popover)",
          "--normal-text": "var(--sc-popover-foreground)",
          "--normal-border": "var(--sc-border)",
          "--border-radius": "var(--sc-radius)"
        }
      }
      {...props} />
  );
}

export { Toaster }

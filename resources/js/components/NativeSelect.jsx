import * as React from 'react';
import * as SelectPrimitive from '@/components/ui/select';
import { ChevronDown } from 'lucide-react';
import { cn } from '@/lib/utils';

const NONE = '__none__';

/**
 * Drop-in replacement for native <NativeSelect> rendered with shadcn/Radix.
 * Accepts the same props (value, onChange with e.target.value, name,
 * children <option> elements), so existing call sites work unchanged.
 */
export default function NativeSelect({ value, onChange, className, id, name, disabled, children }) {
    const options = React.Children.toArray(children)
        .filter((el) => React.isValidElement(el) && el.type === 'option')
        .map((el) => ({
            value: String(el.props.value ?? ''),
            label: el.props.children,
            disabled: !!el.props.disabled,
        }));

    const current = value == null ? '' : String(value);

    const handleValueChange = (v) => {
        const real = v === NONE ? '' : v;
        onChange?.({ target: { name, value: real } });
    };

    return (
        <SelectPrimitive.Root
            // Empty-string options ('All …') are re-keyed to NONE since Radix forbids ''
            value={current === '' ? NONE : current}
            onValueChange={handleValueChange}
            disabled={disabled}
        >
            <SelectPrimitive.Trigger
                id={id}
                className={cn(
                    'flex h-9 w-full items-center justify-between gap-2 rounded-lg border border-edge bg-card px-3 text-sm outline-none transition-colors',
                    'focus:border-brand focus:ring-2 focus:ring-brand/20',
                    'disabled:cursor-not-allowed disabled:opacity-50',
                    className
                )}
            >
                <SelectPrimitive.Value />
                <ChevronDown className="size-4 shrink-0 opacity-60" />
            </SelectPrimitive.Trigger>
            <SelectPrimitive.Portal>
                <SelectPrimitive.Content
                    position="popper"
                    className={cn(
                        'z-[100] max-h-72 min-w-[8rem] overflow-hidden rounded-lg border border-edge bg-popover text-popover-foreground shadow-lg'
                    )}
                >
                    <SelectPrimitive.Viewport className="p-1">
                        {options.map((opt) => (
                            <SelectPrimitive.Item
                                key={opt.value + String(opt.label)}
                                value={opt.value === '' ? NONE : opt.value}
                                disabled={opt.disabled}
                                className={cn(
                                    'relative flex w-full cursor-pointer select-none items-center rounded-md px-2 py-1.5 text-sm outline-none transition-colors',
                                    'focus:bg-dim focus:text-canvas-foreground',
                                    'data-[state=checked]:bg-brand/10 data-[state=checked]:font-medium data-[state=checked]:text-brand',
                                    opt.value === '' && 'text-dim-foreground'
                                )}
                            >
                                <SelectPrimitive.ItemText>{opt.label}</SelectPrimitive.ItemText>
                            </SelectPrimitive.Item>
                        ))}
                    </SelectPrimitive.Viewport>
                </SelectPrimitive.Content>
            </SelectPrimitive.Portal>
        </SelectPrimitive.Root>
    );
}

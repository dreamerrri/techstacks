import * as React from 'react';
import { ChevronDown } from 'lucide-react';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { cn } from '@/lib/utils';

const NONE = '__none__';

/**
 * Drop-in replacement for native <select> rendered with shadcn/Radix.
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
        <Select
            // Empty-string options ('All …') are re-keyed to NONE since Radix forbids ''
            value={current === '' ? NONE : current}
            onValueChange={handleValueChange}
            disabled={disabled}
        >
            <SelectTrigger
                id={id}
                className={cn(
                    'w-full bg-card',
                    className
                )}
            >
                <SelectValue />
            </SelectTrigger>
            <SelectContent position="popper" className="w-[var(--radix-select-trigger-width)]">
                {options.map((opt) => (
                    <SelectItem
                        key={opt.value + String(opt.label)}
                        value={opt.value === '' ? NONE : opt.value}
                        disabled={opt.disabled}
                        className={cn(opt.value === '' && 'text-dim-foreground')}
                    >
                        {opt.label}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}

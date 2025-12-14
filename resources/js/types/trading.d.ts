export type TradingSymbol = 'BTC' | 'ETH';
export type OrderSide = 'buy' | 'sell';
export type OrderStatus = 'open' | 'filled' | 'cancelled';

export interface Asset {
    id: number;
    symbol: TradingSymbol;
    amount: string;
    locked_amount: string;
    available_amount: string;
}

export interface Order {
    id: number;
    symbol: TradingSymbol;
    side: OrderSide;
    price: string;
    amount: string;
    total: string;
    status: OrderStatus;
    created_at: string;
}

export interface OrderbookLevel {
    price: string;
    amount: string;
    total: string;
    count: number;
}

export interface Orderbook {
    symbol: string;
    bids: OrderbookLevel[];
    asks: OrderbookLevel[];
}
